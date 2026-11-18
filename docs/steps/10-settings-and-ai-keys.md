# Step 10: Settings and AI keys

**Goal:** profile, team, notifications, and the creator's own AI keys.

## Do
- Profile, team members and roles, notification choices. Plan and billing later.
- AI keys: one row per provider (Claude, OpenAI, Grok, Groq, others later). Key stored encrypted and shown masked.
  Default model, on or off, Test key button, and a default provider.
- Phase 2 uses these keys for the suggestions panel in the editor.

## Done when
- A key can be saved, tested, masked, and removed.

## Built
- `SettingsController`, `ProfileController`, `AiKeyController`, `app/Settings` (AiProviders, AiKeyTester), models `AiKey` and `Setting`, views in `resources/views/settings`.
- The profile pages step out of the creator schema with `Tenancy::outside()` because users live in `public`.
- A key is write-only: replace, test, toggle, make default, remove. It is never sent to the browser or into an error message (the tester redacts it from the provider's reply).
- Test key calls the provider's model-list endpoint (free, no text generated). The default model names are suggestions the creator can change.
- Notification choices are stored (`settings` table) but nothing is mailed yet.
- Not built: team members and invites (only the owner exists), plan and billing, using the keys for suggestions (phase 2).
- Tests: `AiKeyMaskTest` (any DB), `AiKeyTesterTest` (fake HTTP), `SettingsTest` (PostgreSQL only).
