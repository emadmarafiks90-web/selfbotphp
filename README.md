# EM.ir | Bale PHP Self-Bot

**A PHP-based Bale self-bot project. The first public release focuses on the login/authentication prototype.**

> This repository is an early development release. Additional features, improvements, documentation, and subsequent versions will be announced and published in future releases.

## Project overview

EM.ir Bale PHP Self-Bot is an experimental PHP project intended to establish an authenticated session for the account owner and serve as the foundation for future self-bot features.

The current initial release contains:
- A command-line login flow.
- Phone-number normalization.
- gRPC-Web framing and basic Protocol Buffers helpers.
- A StartPhoneAuth request.
- Verification-code validation.
- Optional two-step password validation.
- Local session-file storage.
- Persistent local device identifier and web-client configuration cache.

**Important:** This is an initial prototype, not a stable or fully verified SDK. Bale may change its web-client protocol, request schemas, headers, or authentication behavior. Successful HTTP status alone does not prove that authentication succeeded. The current implementation has not been independently verified against every account or server response.

## Requirements

- PHP 8.1 or newer
- PHP cURL extension
- Internet connection
- A Bale account that you own and are authorized to use

## Installation

On Debian or Linux Mint:

```bash
sudo apt update
sudo apt install php-cli php-curl
```

Clone this repository:

```bash
git clone https://github.com/emadmarafiks90-web/selfbotphp.git
cd selfbotphp
```

## Run

```bash
php bale.php
```

Enter your phone number with country code 98 when prompted. Follow the prompts for the verification code and, if requested by Bale, the two-step verification password.

All command-line prompts are in English.

## Generated local files

The program may create these files beside `bale.php`:

| File | Purpose |
|---|---|
| `bale.device` | Persistent local device identifier |
| `bale.appkey` | Cached web-client configuration value |
| `bale.session` | Authenticated session response |

These files are ignored by Git. Do not upload, publish, or share them. Treat the session file as a credential.

You can provide the web client configuration value through the `BALE_APP_KEY` environment variable instead of automatic discovery.

## Project status

**Current release:** Initial login prototype.

**Planned for future releases:**
- Better protocol response decoding.
- More robust authentication and session handling.
- Additional self-bot methods.
- Expanded examples and documentation.
- Compatibility improvements and tests.

No release date is promised for future features. Updates will be published when they are ready.

## Responsible use

Use this project only with an account you own or are explicitly authorized to operate. Protect verification codes, passwords, device identifiers, and session files. Respect Bale's terms and applicable laws.

## Copyright and usage restriction

Copyright (c) 2026 EM.ir. All rights reserved.

No permission is granted to copy, reproduce, redistribute, republish, modify, adapt, sublicense, or commercially or non-commercially reuse this project or any part of it, **including when attribution or credit is provided**, without prior written permission from the copyright holder.

This notice expresses the author's intended restrictions. Public availability on GitHub does not itself prevent copying; enforcement and legal effect depend on applicable law and platform processes. No open-source license is granted.
