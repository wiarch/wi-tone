# WI Tone — Security and Privacy

## 1. Audio privacy

The tuner requires microphone access to estimate instrument pitch. The default design is local browser-side analysis.

- Request microphone access only after an explicit user action.
- Explain why microphone access is needed.
- Do not record, upload, persist or stream microphone audio to a server.
- Do not request camera or unrelated permissions.
- Provide a visible stop control.
- Stop all microphone tracks immediately when the tuner is stopped or disposed.
- Do not claim that audio is never processed outside the device if a future feature changes this architecture; update this document first.

## 2. Secure context

Microphone APIs generally require a secure context. Production deployments must use HTTPS. Local development should use localhost or another browser-recognized secure development context.

## 3. Browser API failures

Treat permission denial, missing input devices, unsupported APIs, interrupted streams and AudioContext failures as expected conditions. Show actionable messages and preserve a manual reference-tone fallback where possible.

## 4. Data minimization

The core tuner does not need to store audio or user recordings. If preferences such as instrument, tuning or A4 reference are persisted in the future:
- Store only the minimum required settings.
- Explain persistence and provide a reset path.
- Avoid collecting personal information without a product requirement.
- Apply Laravel authorization and validation to any server-side settings.

## 5. Application security

The Laravel application must continue to follow standard secure development practices:
- Keep secrets in environment variables and never commit real `.env` files.
- Validate and authorize all server-side mutations.
- Use CSRF protection for state-changing web requests.
- Escape user-generated output in Blade.
- Keep dependencies updated and review security advisories.
- Avoid exposing debug output or stack traces in production.
- Apply least privilege to database and deployment credentials.

## 6. Third-party services

No third-party audio-processing service is required by the current tuner design. Any future analytics, crash reporting, telemetry or remote audio feature must receive a privacy review and be documented before integration.

## 7. Security reporting

Do not publish exploitable vulnerabilities in public issues. Report security concerns privately to the repository maintainers using an available private reporting channel. Do not include secrets, personal recordings or sensitive user data in reports.
