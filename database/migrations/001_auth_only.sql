-- Run once on an existing installation after backing up and selecting its database.
-- Removes unused metadata only; account IDs, names, emails, and hashes are unchanged.
ALTER TABLE users
    DROP COLUMN role,
    DROP COLUMN created_at;
