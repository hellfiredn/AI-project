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

async function fetchJson(url, { method = "GET", headers = {}, body } = {}) {
  let res;
  try {
    res = await fetch(url, {
      method,
      headers,
      body,
      signal: AbortSignal.timeout(15_000),
    });
  } catch (err) {
    throw new Error(`${formatNetworkError(err, url)} while fetching ${url}`);
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
    throw new Error(`HTTP ${res.status} ${msg}${code}`);
  }
  return json;
}

export async function wpGetRecentPosts({ postsEndpoint, perPage = 30 }) {
  const url = new URL(postsEndpoint);
  url.searchParams.set("per_page", String(perPage));
  url.searchParams.set("_fields", "id,slug,title,date,link");
  url.searchParams.set("orderby", "date");
  url.searchParams.set("order", "desc");
  return fetchJson(url.toString());
}

export async function wpUploadMedia({
  mediaEndpoint,
  authorUsername,
  appPassword,
  filename,
  bytes,
  mimeType,
  altText,
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
  });

  if (altText) {
    await fetchJson(`${mediaEndpoint}/${media.id}`, {
      method: "POST",
      headers: { Authorization: auth, "Content-Type": "application/json" },
      body: JSON.stringify({ alt_text: altText }),
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
}) {
  const auth = buildBasicAuthHeader(authorUsername, appPassword);
  const listUrl = new URL(`${apiBase}/${taxonomy}`);
  listUrl.searchParams.set("per_page", "100");
  listUrl.searchParams.set("slug", slug);

  const existing = await fetchJson(listUrl.toString(), { headers: { Authorization: auth } });
  if (Array.isArray(existing) && existing[0]?.id) return existing[0].id;
  if (!createMissing) return null;

  const created = await fetchJson(`${apiBase}/${taxonomy}`, {
    method: "POST",
    headers: { Authorization: auth, "Content-Type": "application/json" },
    body: JSON.stringify({ slug, name: name || slug }),
  });
  return created.id;
}
