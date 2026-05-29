ALTER TABLE interest_requests
  ADD COLUMN business_id BIGINT UNSIGNED NULL AFTER pitch_id,
  ADD FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL;
