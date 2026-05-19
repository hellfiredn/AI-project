import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { spawnSync } from "node:child_process";

import { loadDotEnvFile, getRequired } from "./lib/env.mjs";
import { slugifyVi } from "./lib/slug.mjs";
import { buildSeoPost } from "./lib/content.mjs";
import {
  wpGetRecentPosts,
  wpUploadMedia,
  wpCreatePost,
  wpGetOrCreateTerm,
} from "./lib/wp.mjs";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function nowInTZ(timeZone) {
  const date = new Date();
  const formatter = new Intl.DateTimeFormat("sv-SE", {
    timeZone,
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
  return formatter.format(date); // YYYY-MM-DD
}

function nextMonthInfo(todayISO) {
  const [y, m] = todayISO.split("-").map((v) => Number(v));
  const nextMonth = m === 12 ? 1 : m + 1;
  const nextYear = m === 12 ? y + 1 : y;
  const mm = String(nextMonth).padStart(2, "0");
  return {
    year: nextYear,
    month: nextMonth,
    monthLabel: `tháng ${mm}/${nextYear}`,
    monthSlug: `${mm}-${nextYear}`,
    monthShort: `${mm}/${nextYear}`,
  };
}

function safeErrorMessage(err) {
  const msg = err?.message || String(err);
  return msg.replaceAll(/Basic\s+[A-Za-z0-9+/=]+/g, "Basic [REDACTED]");
}

function intFromEnv(env, key, fallback, { min = 0 } = {}) {
  const raw = env[key];
  if (raw == null || raw === "") return fallback;
  const value = Number(raw);
  if (!Number.isFinite(value) || value < min) return fallback;
  return Math.trunc(value);
}

async function main() {
  const envPath = path.join(__dirname, ".env");
  const env = loadDotEnvFile(envPath);

  const websiteUrl = getRequired(env, "WEBSITE_URL");
  const wpApiBase = getRequired(env, "WP_API_BASE");
  const postsEndpoint = getRequired(env, "WP_POSTS_ENDPOINT");
  const mediaEndpoint = getRequired(env, "WP_MEDIA_ENDPOINT");

  const author = getRequired(env, "WEBSITE_AUTHOR");
  const appPass = getRequired(env, "WEBSITE_APP_PASS");

  const status = env.WP_POST_STATUS || "publish";
  const createMissingTerms = (env.WP_CREATE_MISSING_TERMS || "true").toLowerCase() === "true";
  const defaultCategorySlug = env.WP_DEFAULT_CATEGORY_SLUG || "coupon";
  const defaultTags = (env.WP_DEFAULT_TAGS || "")
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);

  const timeZone = env.CONTENT_TIMEZONE || "Asia/Ho_Chi_Minh";
  const requestOptions = {
    timeoutMs: intFromEnv(env, "WP_FETCH_TIMEOUT_MS", 20_000, { min: 1 }),
    retries: intFromEnv(env, "WP_FETCH_RETRIES", 3),
    retryDelayMs: intFromEnv(env, "WP_FETCH_RETRY_DELAY_MS", 2_000),
  };
  const todayISO = nowInTZ(timeZone);
  const { monthLabel, monthSlug, monthShort } = nextMonthInfo(todayISO);

  // Rotate topics by day-of-month to reduce duplicate themes.
  const dayOfMonth = Number(todayISO.split("-")[2] || "1");
  // 0 mod 4: comparisons (avoid repeating monthly calendar on even days)
  // even: sale calendar, odd: voucher checklist
  const topicKind =
    dayOfMonth % 4 === 0 ? "compare_marketplaces" : dayOfMonth % 2 === 0 ? "sale_calendar" : "voucher_checklist";

  const topic = (() => {
    if (topicKind === "compare_marketplaces") {
      const seoTitle = `So sánh nơi mua rẻ hàng tạp hóa online: Shopee vs Lazada vs Tiki vs TikTok Shop (cập nhật ${todayISO})`;
      const metaDescription =
        "Gợi ý cách so sánh giá thật sau mã/ship, mẹo chọn shop uy tín và checklist 5 bước giúp chốt đơn rẻ hơn trên Shopee, Lazada, Tiki, TikTok Shop.";
      const slugBase = slugifyVi(`so-sanh-noi-mua-re-hang-tap-hoa-online-shopee-lazada-tiki-tiktok-shop-${todayISO}`);
      return {
        kind: "compare_marketplaces",
        seoTitle,
        metaDescription,
        slugBase,
        imageTitle: "So sánh nơi mua rẻ",
        monthLabel: null,
      };
    }

    if (topicKind === "sale_calendar") {
      const seoTitle = `Lịch sale ${monthShort}: mốc canh voucher & freeship Shopee/Tiki/Lazada/TikTok Shop`;
      const metaDescription =
        `Tổng hợp mốc sale ${monthShort} theo sàn + checklist chuẩn bị giỏ, ưu tiên mã freeship/mã sàn và mẹo chốt đơn đúng khung giờ.`;
      const slugBase = slugifyVi(`lich-sale-thang-${monthSlug}-shopee-tiki-lazada-tiktok-shop`);
      return {
        kind: "sale_calendar",
        seoTitle,
        metaDescription,
        slugBase,
        imageTitle: `Lịch sale ${monthShort}`,
        monthLabel,
      };
    }

    const seoTitle = `Checklist săn voucher & freeship hiệu quả (Shopee/Tiki/Lazada/TikTok Shop) – cập nhật ${todayISO}`;
    const metaDescription =
      "Hướng dẫn nhanh 6 bước săn voucher, freeship, hoàn xu: chuẩn bị giỏ, ưu tiên mã, xử lý lỗi không áp dụng và tối ưu theo khung giờ sale.";
    const slugBase = slugifyVi(`checklist-san-voucher-freeship-shopee-tiki-lazada-tiktok-shop-${todayISO}`);
    return {
      kind: "voucher_checklist",
      seoTitle,
      metaDescription,
      slugBase,
      imageTitle: "Checklist săn voucher & freeship",
      monthLabel: null,
    };
  })();

  const { contentHtml } = buildSeoPost({
    kind: topic.kind,
    todayISO,
    seoTitle: topic.seoTitle,
    metaDescription: topic.metaDescription,
    disclosure: env.CONTENT_DISCLOSURE || "",
    couponUrl: env.WEBSITE_COUPON_URL || `${websiteUrl}/coupon/`,
    dealUrl: env.WEBSITE_DEAL_URL || `${websiteUrl}/deal/`,
    monthLabel: topic.monthLabel,
  });

  const outDir = path.join(__dirname, "out");
  fs.mkdirSync(outDir, { recursive: true });

  const imageFilename = `${topic.slugBase}.webp`;
  const imagePath = path.join(outDir, imageFilename);
  const gen = spawnSync(
    "python3",
    [
      path.join(__dirname, "gen_featured_image.py"),
      "--title",
      topic.imageTitle,
      "--out",
      imagePath,
      "--w",
      String(env.IMAGE_MAIN_WIDTH || 1200),
      "--h",
      String(env.IMAGE_MAIN_HEIGHT || 630),
      "--brand",
      env.IMAGE_ALT_PREFIX || "Tạp Hóa Giảm Giá",
      "--date",
      todayISO.split("-").reverse().join("/"),
    ],
    { stdio: "inherit" },
  );
  if (gen.status !== 0) {
    throw new Error("Image generation failed");
  }

  // Always persist a local draft bundle (useful when network is blocked).
  const draftPath = path.join(outDir, `${topic.slugBase}.draft.json`);
  fs.writeFileSync(
    draftPath,
    JSON.stringify(
      {
        title: topic.seoTitle,
        slug: topic.slugBase,
        excerpt: topic.metaDescription,
        contentHtml,
        featuredImagePath: imagePath,
        featuredImageAlt: `${env.IMAGE_ALT_PREFIX || "Tạp Hóa Giảm Giá"} - ${topic.seoTitle}`,
        generatedAt: new Date().toISOString(),
      },
      null,
      2,
    ),
    "utf8",
  );

  // Fetch recent posts to avoid slug duplication when possible.
  let recentPosts = [];
  try {
    recentPosts = await wpGetRecentPosts({ postsEndpoint, perPage: 50, requestOptions });
  } catch (e) {
    throw new Error(
      `Cannot reach WordPress API at ${postsEndpoint}. Network/DNS might be blocked in this environment. Details: ${safeErrorMessage(
        e,
      )}`,
    );
  }
  const existingSlugs = new Set(recentPosts.map((p) => p.slug));
  let finalSlug = topic.slugBase;
  if (existingSlugs.has(finalSlug)) finalSlug = `${topic.slugBase}-${Date.now().toString(36)}`;

  // Terms
  const categoryId = await wpGetOrCreateTerm({
    apiBase: wpApiBase,
    taxonomy: "categories",
    authorUsername: author,
    appPassword: appPass,
    slug: defaultCategorySlug,
    name: defaultCategorySlug,
    createMissing: createMissingTerms,
    requestOptions,
  });
  const tagIds = [];
  for (const t of defaultTags) {
    const tagSlug = slugifyVi(t);
    const tagId = await wpGetOrCreateTerm({
      apiBase: wpApiBase,
      taxonomy: "tags",
      authorUsername: author,
      appPassword: appPass,
      slug: tagSlug,
      name: t,
      createMissing: createMissingTerms,
      requestOptions,
    });
    if (tagId) tagIds.push(tagId);
  }

  // Upload media
  const bytes = fs.readFileSync(imagePath);
  const altText = `${env.IMAGE_ALT_PREFIX || "Tạp Hóa Giảm Giá"} - ${topic.seoTitle}`;
  const media = await wpUploadMedia({
    mediaEndpoint,
    authorUsername: author,
    appPassword: appPass,
    filename: imageFilename,
    bytes,
    mimeType: "image/webp",
    altText,
    requestOptions,
  });

  // Create post
  const post = await wpCreatePost({
    postsEndpoint,
    authorUsername: author,
    appPassword: appPass,
    title: topic.seoTitle,
    slug: finalSlug,
    contentHtml,
    excerpt: topic.metaDescription,
    status,
    categories: categoryId ? [categoryId] : [],
    tags: tagIds,
    featuredMediaId: media?.id,
    requestOptions,
  });

  // Verify public URL
  const publicUrl = post?.link;
  if (!publicUrl) throw new Error("Publish succeeded but post link missing from response.");
  const verifyRes = await fetch(publicUrl, { method: "GET" });
  if (!verifyRes.ok) throw new Error(`Published but public URL not reachable: HTTP ${verifyRes.status}`);

  console.log(JSON.stringify({
    title: topic.seoTitle,
    slug: finalSlug,
    postUrl: publicUrl,
    mediaUrl: media?.source_url || null,
    mediaId: media?.id || null,
  }, null, 2));
}

main().catch((err) => {
  console.error(safeErrorMessage(err));
  process.exitCode = 1;
});
