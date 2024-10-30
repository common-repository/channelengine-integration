# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [Unreleased]

## [1.0.4] - 2023-11-21

- Update order DTO with type of fulfillment information.

## [1.0.3] - 2023-10-05

- Added new method for creating returns in returns Proxy and ReturnsService.
- Turn on/off product synchronization.
- Fix saving and getting last sync time

## [1.0.2] - 2022-09-09

- Fixed bug in TransactionLog FailedListener.
- Fixed error handling for products upsert.
- Fixed cancellation request handling.
- On ChannelEngine API, 'X-Rate-Limit-Reset' header changed to 'retry-after'.
  Handling of 429 response in Proxy changed according to this.

## [1.0.1] - 2022-04-29

- Changed product upsert batch size to 150.

## [1.0.0] - 2022-04-26

- Improved transaction log in the product delete task.
- Added support for real account info instead of the mocked data.
- Improved transaction log listeners.
- Added context to Notification, Details and TransactionLog entities.
- Added parameter `query` to NotificationService::countNotRead method.
- Added ProductEventHandlerTask.
- In TickEventListener instead of handling new ProductEvents, ProductEventHandlerTask is enqueued.
- Added context when enqueuing OrderSync task in WebhooksHandler.
- Added returns webhooks handling.
- Added VatRate property to LineItem DTO.

**Breaking:**

- Removed the `getApiUrl` method in the `AccountInfo` class.
- Removed the `setApiUrl` method in the `AccountInfo` class.
- Orders Proxy: Method `getClosed` changed to `getWithStatuses`.
- `EventTypes::ORDERS_CHANGE` renamed to `EventTypes::ORDERS_CREATE`.
- Removed TickEventListener registration from BootstrapComponent.
- Changed WebhooksHandler to be abstract class. Logic for order webhook handling moved to OrderWebhookHandler.