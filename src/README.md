# studio.logged.cloud

Pet snake husbandry log for the [logged.cloud](https://logged.cloud) family.
Mobile-first PWA: track feeding, weight, sheds, handling, and get a push
notification with Fed / Refused / Snooze actions when a snake is due to eat.

Sister app to [fish.logged.cloud](https://github.com/Logged-Cloud/fish.logged.cloud).
Identity, role and the mobile nav shell come from the shared platform
(`logged-cloud/auth-client`, `logged-cloud/navigation`).

## Stack

- Laravel 13, Livewire 4, Tailwind v4, Alpine 3
- SSO via auth.logged.cloud (OAuth2 + PKCE)
- PWA push via `minishlink/web-push`
- Pest + Dusk

## Local dev

The deploy lives in `/var/www/studio-logged/` on the host (Docker Compose stack
bound to host port 8105). Production URL: <https://studio.logged.cloud>.
