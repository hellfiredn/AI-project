# Daily Facebook fanpage automation

Use this workflow for the Codex daily automation.

1. Read `social-media-fanpage/content-calendar.xlsx` for topic mix, cadence, tone, and existing examples.
2. Generate one Vietnamese Facebook post for the current date. Prioritize the existing calendar row if today is listed; otherwise rotate these pillars:
   - Yến sào product/trust building
   - Yến sào tips/education
   - Customer review/social proof
   - Affiliate deal/mã giảm giá
   - Engagement question or mini-game
3. Keep the post natural and non-spammy:
   - No unverifiable health guarantees.
   - No all-caps caption.
   - Use a modest number of emojis.
   - For affiliate posts, keep product links out of the caption and place links in the optional first comment file.
4. Save the caption to `social-media-fanpage/outbox/YYYY-MM-DD-facebook-caption.txt`.
5. If a first comment is needed, save it to `social-media-fanpage/outbox/YYYY-MM-DD-first-comment.txt`.
6. If `social-media-fanpage/facebook-env.local.ps1` exists, load it before publishing:

```powershell
. .\social-media-fanpage\facebook-env.local.ps1
```

7. Publish with:

```powershell
.\social-media-fanpage\publish-facebook-page-post.ps1 -MessageFile .\social-media-fanpage\outbox\YYYY-MM-DD-facebook-caption.txt
```

For affiliate posts with a first comment:

```powershell
.\social-media-fanpage\publish-facebook-page-post.ps1 -MessageFile .\social-media-fanpage\outbox\YYYY-MM-DD-facebook-caption.txt -FirstCommentFile .\social-media-fanpage\outbox\YYYY-MM-DD-first-comment.txt
```

Required environment variables:

- `FACEBOOK_PAGE_ID`
- `FACEBOOK_PAGE_ACCESS_TOKEN`
- `FACEBOOK_GRAPH_API_VERSION`

Keep `FACEBOOK_DRY_RUN=1` until the full flow has been tested. Set it to `0` only when automatic publishing is ready.
