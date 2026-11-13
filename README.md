# EM.ir | Bale PHP Self-Bot

**A PHP-based Bale self-bot project. The first public release focuses on the login/authentication prototype.**

**یک پروژه سلف‌بات بله با زبان PHP؛ نسخه اولیه بر نمونه اولیه ورود و احراز هویت تمرکز دارد.**

> This is an early development release. Future versions, improvements, examples, and documentation will be published as they become ready.
>
> این نسخه، انتشار اولیه و آزمایشی پروژه است. نسخه‌های بعدی، بهبودها، نمونه‌ها و مستندات پس از آماده‌شدن منتشر خواهند شد.

---

## English

### Overview
EM.ir Bale PHP Self-Bot is an experimental PHP project intended to establish an authenticated session for an account owner and provide a foundation for future self-bot features.

Current prototype includes:
- Command-line authentication flow
- Phone-number normalization
- gRPC-Web framing and basic Protocol Buffers helpers
- StartPhoneAuth request
- Verification-code validation
- Optional two-step password validation
- Local session response storage

**Status:** Experimental prototype, not a stable or fully verified SDK. Bale may change its web-client protocol, request schemas, headers, or authentication behavior. The implementation has not been independently verified against every account or server response.

### Requirements
- PHP 8.1+
- PHP cURL extension
- Internet connection
- A Bale account you own or are authorized to use

### Install (Debian / Linux Mint)
```bash
sudo apt update
sudo apt install php-cli php-curl
git clone https://github.com/emadmarafiks90-web/selfbotphp.git
cd selfbotphp
php bale.php
```

Enter your phone number with country code 98, then follow the verification prompts. All CLI prompts are in English.

### Local files
| File | Purpose |
|---|---|
| `bale.device` | Persistent local device identifier |
| `bale.appkey` | Cached web-client configuration |
| `bale.session` | Authentication response |

These files are ignored by Git. Treat `bale.session` as a credential. Never publish or share it. You may set `BALE_APP_KEY` in the environment to provide the web-client configuration value manually.

### Roadmap
- Improved protocol response decoding
- More robust authentication and session handling
- Additional self-bot methods
- More examples, tests, and documentation
- Compatibility improvements

No release date is promised for future features.

### Responsible use
Use this project only with an account you own or are explicitly authorized to operate. Protect verification codes, passwords, device identifiers, and session files. Respect Bale's terms and applicable laws.

---

## فارسی

### معرفی پروژه
EM.ir Bale PHP Self-Bot یک پروژه آزمایشی با زبان PHP است که با هدف پیاده‌سازی فرایند ورود حساب شخصی بله و ایجاد پایه‌ای برای توسعه قابلیت‌های بعدی ساخته شده است.

قابلیت‌های نسخه اولیه:
- فرایند ورود از طریق ترمینال
- یکسان‌سازی قالب شماره تلفن
- ابزارهای پایه برای gRPC-Web و Protocol Buffers
- ارسال درخواست StartPhoneAuth
- بررسی کد تأیید
- پشتیبانی اولیه از رمز دومرحله‌ای
- ذخیره پاسخ نشست به‌صورت محلی

**وضعیت پروژه:** این نسخه صرفاً یک نمونه اولیه آزمایشی است و هنوز یک کتابخانه پایدار یا کاملاً تأییدشده محسوب نمی‌شود. ممکن است ساختار درخواست‌ها، هدرها و شیوه احراز هویت بله تغییر کند. عملکرد آن برای تمام حساب‌ها و پاسخ‌های سرور به‌طور مستقل تأیید نشده است.

### پیش‌نیازها
- PHP نسخه 8.1 یا بالاتر
- افزونه cURL برای PHP
- اتصال اینترنت
- حساب بله‌ای که مالک آن هستید یا اجازه استفاده از آن را دارید

### نصب و اجرا در لینوکس مینت
```bash
sudo apt update
sudo apt install php-cli php-curl
git clone https://github.com/emadmarafiks90-web/selfbotphp.git
cd selfbotphp
php bale.php
```

شماره تلفن را با کد کشور 98 وارد کنید و مراحل تأیید را ادامه دهید. تمام پیام‌های ورودی ترمینال به زبان انگلیسی هستند.

### فایل‌های محلی
| نام فایل | کاربرد |
|---|---|
| `bale.device` | شناسه محلی دستگاه |
| `bale.appkey` | پیکربندی ذخیره‌شده وب‌کلاینت |
| `bale.session` | پاسخ احراز هویت |

این فایل‌ها توسط Git نادیده گرفته می‌شوند. فایل `bale.session` را مانند رمز عبور محرمانه نگه دارید و آن را منتشر یا برای دیگران ارسال نکنید. برای تعیین دستی مقدار پیکربندی وب‌کلاینت می‌توانید متغیر محیطی `BALE_APP_KEY` را تنظیم کنید.

### برنامه توسعه
- بهبود تجزیه پاسخ‌های پروتکل
- مدیریت مطمئن‌تر ورود و نشست
- افزودن متدهای بیشتر سلف‌بات
- نمونه‌ها، تست‌ها و مستندات تکمیلی
- افزایش سازگاری با تغییرات بله

برای انتشار قابلیت‌های آینده تاریخ قطعی اعلام نشده است.

### استفاده مسئولانه
از پروژه فقط برای حسابی استفاده کنید که متعلق به شماست یا مجوز استفاده از آن را دارید. کدهای تأیید، رمزها، شناسه دستگاه و فایل نشست را محرمانه نگه دارید و قوانین و شرایط استفاده بله را رعایت کنید.

---

## Copyright / حقوق نشر

Copyright (c) 2026 EM.ir. All rights reserved.

No permission is granted to copy, reproduce, redistribute, republish, modify, adapt, sublicense, or reuse this project or any part of it, commercially or non-commercially, **even with attribution**, without prior written permission from the copyright holder.

تمام حقوق این پروژه محفوظ است. هرگونه کپی، بازنشر، توزیع، تغییر یا استفاده مجدد از تمام یا بخشی از پروژه، چه تجاری و چه غیرتجاری و حتی با ذکر نام سازنده، بدون دریافت اجازه کتبی قبلی ممنوع است.

This is a proprietary project; no open-source license is granted. The legal effect of this notice depends on applicable law.
