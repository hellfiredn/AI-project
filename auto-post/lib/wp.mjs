import { Buffer } from "node:buffer";

function buildBasicAuthHeader(username, appPassword) {
  const token = Buffer.from(`${username}:${appPassword}`, "utf8").toString("base64");
  return `Basic ${token}`;
}

function formatNetworkError(err, url) {
  const hostname = (() => {
    try {
      return new URL(url).hostname;
    } catch {
      return null;
    }
  })();

  const cause = err?.cause;
  const code = cause?.code || err?.code;
  const syscall = cause?.syscall || err?.syscall;

  const bits = [];
  if (hostname) bits.push(`host=${hostname}`);
  if (code) bits.push(`code=${code}`);
  if (syscall) bits.push(`syscall=${syscall}`);
  const suffix = bits.length ? ` (${bits.join(" ")})` : "";

  const msg = err?.message || String(err);
  return `Network error: ${msg}${suffix}`;
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function shouldRetryRequest({ method, status }) {
  const retryableMethod = method === "GET" || method === "HEAD";
  if (!retryableMethod) return false;
  if (status == null) return true;
  return status === 408 || status === 425 || status === 429 || status >= 500;
}

function intOption(value, fallback, { min = 0 } = {}) {
  const parsed = Number(value);
  if (!Number.isFinite(parsed) || parsed < min) return fallback;
  return Math.trunc(parsed);
}

async function fetchJson(
  url,
  {
    method = "GET",
    headers = {},
    body,
    timeoutMs = 15_000,
    retries = 2,
    retryDelayMs = 1_500,
  } = {},
) {
  const normalizedMethod = method.toUpperCase();
  const timeout = intOption(timeoutMs, 15_000, { min: 1 });
  const retryCount = intOption(retries, 2);
  const retryDelay = intOption(retryDelayMs, 1_500);
  const maxAttempts = shouldRetryRequest({ method: normalizedMethod, status: null })
    ? retryCount + 1
    : 1;

  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
    let res;
    try {
      res = await fetch(url, {
        method: normalizedMethod,
        headers,
        body,
        signal: AbortSignal.timeout(timeout),
      });
    } catch (err) {
      const msg = `${formatNetworkError(err, url)} while fetching ${url}`;
      if (attempt < maxAttempts) {
        const delayMs = retryDelay * attempt;
        console.warn(`${msg}; retrying in ${delayMs}ms (attempt ${attempt}/${maxAttempts})`);
        await sleep(delayMs);
        continue;
      }
      throw new Error(maxAttempts > 1 ? `${msg} (attempt ${attempt}/${maxAttempts})` : msg);
    }

    const text = await res.text();
    let json;
    try {
      json = text ? JSON.parse(text) : null;
    } catch {
      json = null;
    }
    if (!res.ok) {
      const msg = json?.message || res.statusText || "Request failed";
      const code = json?.code ? ` (${json.code})` : "";
      const error = new Error(`HTTP ${res.status} ${msg}${code}`);
      if (attempt < maxAttempts && shouldRetryRequest({ method: normalizedMethod, status: res.status })) {
        const delayMs = retryDelay * attempt;
        console.warn(
          `${error.message} while fetching ${url}; retrying in ${delayMs}ms (attempt ${attempt}/${maxAttempts})`,
        );
        await sleep(delayMs);
        continue;
      }
      throw error;
    }
    return json;
  }
}

export async function wpGetRecentPosts({ postsEndpoint, perPage = 30, requestOptions = {} }) {
  const url = new URL(postsEndpoint);
  url.searchParams.set("per_page", String(perPage));
  url.searchParams.set("_fields", "id,slug,title,date,link");
  url.searchParams.set("orderby", "date");
  url.searchParams.set("order", "desc");
  return fetchJson(url.toString(), requestOptions);
}

export async function wpUploadMedia({
  mediaEndpoint,
  authorUsername,
  appPassword,
  filename,
  bytes,
  mimeType,
  altText,
  requestOptions = {},
}) {
  const auth = buildBasicAuthHeader(authorUsername, appPassword);
  const headers = {
    Authorization: auth,
    "Content-Disposition": `attachment; filename="${filename}"`,
    "Content-Type": mimeType,
  };
  const media = await fetchJson(mediaEndpoint, {
    method: "POST",
    headers,
    body: bytes,
    ...requestOptions,
  });

  if (altText) {
    await fetchJson(`${mediaEndpoint}/${media.id}`, {
      method: "POST",
      headers: { Authorization: auth, "Content-Type": "application/json" },
      body: JSON.stringify({ alt_text: altText }),
      ...requestOptions,
    });
  }

  return media;
}

export async function wpCreatePost({
  postsEndpoint,
  authorUsername,
  appPassword,
  title,
  slug,
  contentHtml,
  excerpt,
  status,
  categories,
  tags,
  featuredMediaId,
  requestOptions = {},
}) {
  const auth = buildBasicAuthHeader(authorUsername, appPassword);
  const payload = {
    title,
    slug,
    status,
    content: contentHtml,
    excerpt,
  };
  if (Array.isArray(categories) && categories.length) payload.categories = categories;
  if (Array.isArray(tags) && tags.length) payload.tags = tags;
  if (featuredMediaId) payload.featured_media = featuredMediaId;

  return fetchJson(postsEndpoint, {
    method: "POST",
    headers: { Authorization: auth, "Content-Type": "application/json" },
    body: JSON.stringify(payload),
    ...requestOptions,
  });
}

export async function wpGetOrCreateTerm({
  apiBase,
  taxonomy, // "categories" | "tags"
  authorUsername,
  appPassword,
  slug,
  name,
  createMissing = true,
  requestOptions = {},
}) {
  const auth = buildBasicAuthHeader(authorUsername, appPassword);
  const listUrl = new URL(`${apiBase}/${taxonomy}`);
  listUrl.searchParams.set("per_page", "100");
  listUrl.searchParams.set("slug", slug);

  const existing = await fetchJson(listUrl.toString(), {
    headers: { Authorization: auth },
    ...requestOptions,
  });
  if (Array.isArray(existing) && existing[0]?.id) return existing[0].id;
  if (!createMissing) return null;

  const created = await fetchJson(`${apiBase}/${taxonomy}`, {
    method: "POST",
    headers: { Authorization: auth, "Content-Type": "application/json" },
    body: JSON.stringify({ slug, name: name || slug }),
    ...requestOptions,
  });
  return created.id;
}
