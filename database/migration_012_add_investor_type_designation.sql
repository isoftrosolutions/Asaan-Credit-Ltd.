ALTER TABLE users
  ADD COLUMN investor_type VARCHAR(50) DEFAULT NULL AFTER usage_goal,
  ADD COLUMN designation VARCHAR(255) DEFAULT NULL AFTER investor_type;
