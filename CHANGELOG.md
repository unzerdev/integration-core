# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [Unreleased](https://github.com/unzerdev/integration-core/compare/master...dev)
- Add separate success, failure, pending and cancel return URLs for the payment page (`ReturnUrls`, optional on `PaymentPageCreateRequest`; a single return URL is still used for every outcome when omitted)
- Report which step failed while starting a payment: `CustomerCreationException`, `MetadataCreationException`, `PaypageCreationException` and `PaymentCreationException` (original Unzer error kept as the previous exception). These failures were reported as `general.unhandled` before
- Add optional `PaymentResourceListener` integration hook, notified when Unzer refuses the basket (the payment continues without it)
- `OrderManagementService::chargeOrder()` returns a `ChargeResult`: the charge sent to Unzer, or the reason nothing was sent (`ChargeSkipReason`). `ChargeResponse::getResult()` exposes it to the Admin API caller
- Add integration hooks `fetchTransactionHistoryForCharge()` (e.g. throw `ChargePaymentNotReadableException` instead of returning no history on an Unzer error) and `getChargeSkipReason()`; overrides of `isChargeNecessary()` keep working
- `OrderManagementService::refundOrder()` returns a `RefundResult`: the cancellations sent to Unzer, the reason nothing was sent (`RefundSkipReason`), or a partial refund (some charges refunded, then Unzer failed). `refundOrderByPayment()` returns the `Cancellation`. `RefundResponse::getResult()` exposes the result. Subclasses overriding either method with a `void` return type must be updated
- Refunds above what is still refundable on the charges are no longer sent (`AMOUNT_EXCEEDS_REFUNDABLE`); before, only the remaining part was refunded and the call returned as if the full amount had been
- Add integration hooks `fetchTransactionHistoryForRefund()` and `getRefundSkipReason()`; overrides of `isRefundNecessary()` keep working

## [2.2.6](https://github.com/unzerdev/integration-core/releases/tag/2.2.6) - 2026-10-29
- Fix refund logic when one of charge item failed

## [2.2.5](https://github.com/unzerdev/integration-core/releases/tag/2.2.5) - 2026-10-05
- Improve logging and error handling logic

## [2.2.4](https://github.com/unzerdev/integration-core/releases/tag/2.2.4) - 2026-09-17
- Set Wero to inline flow 
- Add integration specific data clean up on disconnect

## [2.2.3](https://github.com/unzerdev/integration-core/releases/tag/2.2.3) - 2026-09-01
- Update admin-ui package

## [2.2.2](https://github.com/unzerdev/integration-core/releases/tag/2.2.2) - 2026-08-14
- Add Payment page v2 deletion logic

## [2.2.1](https://github.com/unzerdev/integration-core/releases/tag/2.2.1) - 2026-06-30
- Drop support for PHP 7.4 and 8.0
- Add support for PHP 8.5
- Update Unzer SDK version to [4.0.1](https://github.com/unzerdev/php-sdk/releases/tag/4.0.1)

## [2.2.0](https://github.com/unzerdev/integration-core/releases/tag/2.2.0) - 2026-05-04
- Add a reference field in cancel, charge and refund requests
- Create Basket resource for inline payments
- Add support for updating existing customer on Unzer

## [2.1.2](https://github.com/unzerdev/integration-core/releases/tag/2.1.2) - 2026-04-17
- Add locale to PayPage redirect URL to support translations

## [2.1.1](https://github.com/unzerdev/integration-core/releases/tag/2.1.1) - 2026-03-23
- Enrich PayPage with:
  - subscriptionAgreement
  - paymentFormBackgroundColor 
  - basketBackgroundColor
- Updated Unzer SDK version to 3.15.0

## [2.1.0](https://github.com/unzerdev/integration-core/releases/tag/2.1.0) - 2026-03-09
- Add additional links on Payment page 
- Add Favicon image on Payment page
- Enhance URL validation logic

## [2.0.1](https://github.com/unzerdev/integration-core/releases/tag/2.0.1) - 2026-02-27
- Enhance creating Paypage logic
- Resolve Dependabot alerts

## [2.0.0](https://github.com/unzerdev/integration-core/releases/tag/2.0.0) - 2025-11-12
- Add support for:
    - [Inline payments](https://docs.unzer.com/online-payments/integrate-only-server-side/)

## [1.2.0](https://github.com/unzerdev/integration-core/releases/tag/1.2.0) - 2025-06-11
- Add support for:
  - [Unzer Direct Bank Transfer](https://docs.unzer.com/payment-methods/open-banking/?_gl=1*4n1fg5*_up*MQ..*_ga*NDE3NjA2ODguMTc0NzcyOTUyOA..*_ga_KQLTE7404W*czE3NDc3Mjk1MjgkbzEkZzEkdDE3NDc3MzAwMDMkajAkbDAkaDA)
  - [ApplePay](https://docs.unzer.com/payment-methods/applepay/?_gl=1*i2diqw*_up*MQ..*_ga*NDE3NjA2ODguMTc0NzcyOTUyOA..*_ga_KQLTE7404W*czE3NDc3Mjk1MjgkbzEkZzEkdDE3NDc3Mjk1MjkkajAkbDAkaDA)
  - [Click to Pay](https://docs.unzer.com/payment-methods/card/?_gl=1*1dhyjxo*_up*MQ..*_ga*NDE3NjA2ODguMTc0NzcyOTUyOA..*_ga_KQLTE7404W*czE3NDc3Mjk1MjgkbzEkZzEkdDE3NDc3Mjk2MDckajAkbDAkaDA)
- Fixed:
  - Order actions when Transaction history entity is not found
- Changed:
  - Set checkout type to payment_only for all payments in [Create new pay page](https://docs.unzer.com/reference/api/payment-page-api-reference-v2/#tag/Manage-Payment-Page/operation/createPayPage) request

## [1.1.0](https://github.com/unzerdev/integration-core/releases/tag/1.1.0) - 2025-03-20
- Migrate to Payment Page v2

## [1.0.0](https://github.com/unzerdev/integration-core/releases/tag/1.0.0) - 2025-01-29
- First stable release
