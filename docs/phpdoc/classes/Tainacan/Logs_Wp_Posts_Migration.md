# Logs_Wp_Posts_Migration

Handles migration of legacy Tainacan logs from wp_posts/wp_postmeta
to the dedicated tainacan_logs table.

Designed to be called from both WP-CLI commands and Action Scheduler jobs.
Uses _wp_posts_log_migration_ref as an idempotency key so both operations
can be run multiple times safely without side effects.

***

* Full name: `\Tainacan\Logs_Wp_Posts_Migration`

## Methods

### count_pending

Count how many legacy tainacan-log posts have not yet been migrated.

```php
public count_pending(): int
```

***

### run_batch

Migrate a single batch of legacy logs to tainacan_logs.

```php
public run_batch(int $batch_size = 50): array
```

Uses LEFT JOIN on _wp_posts_log_migration_ref so already-migrated
records are automatically excluded — safe to call repeatedly.

**Parameters:**

| Parameter     | Type    | Description                                |
|---------------|---------|--------------------------------------------|
| `$batch_size` | **int** | Number of records to process in this call. |

**Return Value:**

{ migrated: int, pending: int }

***

### count_purgeable

Count how many already-migrated wp_posts log records are eligible for purging.

```php
public count_purgeable(): int
```

***

### purge_batch

Delete a batch of already-migrated tainacan-log records from wp_posts and wp_postmeta.

```php
public purge_batch(int $batch_size = 50): array
```

Only removes records whose ID is present in tainacan_logs._wp_posts_log_migration_ref,
so non-migrated logs are never touched. Safe to call repeatedly.

**Parameters:**

| Parameter     | Type    | Description                               |
|---------------|---------|-------------------------------------------|
| `$batch_size` | **int** | Number of records to delete in this call. |

**Return Value:**

{ deleted: int, pending: int }

***
