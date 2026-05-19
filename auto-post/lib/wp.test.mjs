import assert from "node:assert/strict";
import test from "node:test";

import { wpCreatePost, wpGetRecentPosts } from "./wp.mjs";

const originalFetch = globalThis.fetch;
const originalWarn = console.warn;

test.after(() => {
  globalThis.fetch = originalFetch;
  console.warn = originalWarn;
});

test("wpGetRecentPosts retries transient GET network failures", async (t) => {
  const warnings = [];
  let calls = 0;
  console.warn = (message) => warnings.push(message);
  globalThis.fetch = async (url, options) => {
    calls += 1;
    assert.equal(options.method, "GET");
    assert.ok(options.signal instanceof AbortSignal);
    if (calls < 3) {
      const err = new Error("fetch failed");
      err.cause = { code: "UND_ERR_CONNECT_TIMEOUT" };
      throw err;
    }

    const parsed = new URL(url);
    assert.equal(parsed.searchParams.get("per_page"), "1");
    assert.equal(parsed.searchParams.get("orderby"), "date");
    return new Response(JSON.stringify([{ id: 7, slug: "hello-world" }]), { status: 200 });
  };
  t.after(() => {
    globalThis.fetch = originalFetch;
    console.warn = originalWarn;
  });

  const posts = await wpGetRecentPosts({
    postsEndpoint: "https://example.test/wp-json/wp/v2/posts",
    perPage: 1,
    requestOptions: { retries: 2, retryDelayMs: 0, timeoutMs: 1_000 },
  });

  assert.deepEqual(posts, [{ id: 7, slug: "hello-world" }]);
  assert.equal(calls, 3);
  assert.equal(warnings.length, 2);
  assert.match(warnings[0], /attempt 1\/3/);
});

test("wpCreatePost does not retry non-idempotent POST failures", async (t) => {
  let calls = 0;
  globalThis.fetch = async () => {
    calls += 1;
    const err = new Error("fetch failed");
    err.cause = { code: "ECONNRESET" };
    throw err;
  };
  t.after(() => {
    globalThis.fetch = originalFetch;
  });

  await assert.rejects(
    wpCreatePost({
      postsEndpoint: "https://example.test/wp-json/wp/v2/posts",
      authorUsername: "author",
      appPassword: "app-pass",
      title: "Title",
      slug: "title",
      contentHtml: "<p>Hello</p>",
      excerpt: "Hello",
      status: "draft",
      requestOptions: { retries: 5, retryDelayMs: 0, timeoutMs: 1_000 },
    }),
    /Network error: fetch failed/,
  );

  assert.equal(calls, 1);
});
