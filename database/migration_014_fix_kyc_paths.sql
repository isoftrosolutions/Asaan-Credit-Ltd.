-- Fix KYC document paths that are missing the kyc-documents/ prefix
-- handle_upload() returns only the filename; new code now prepends the prefix.
UPDATE kyc_verifications
SET id_front_path = CONCAT('kyc-documents/', id_front_path)
WHERE id_front_path IS NOT NULL
  AND id_front_path != ''
  AND id_front_path NOT LIKE 'kyc-documents/%';

UPDATE kyc_verifications
SET id_back_path = CONCAT('kyc-documents/', id_back_path)
WHERE id_back_path IS NOT NULL
  AND id_back_path != ''
  AND id_back_path NOT LIKE 'kyc-documents/%';

UPDATE kyc_verifications
SET registration_cert_path = CONCAT('kyc-documents/', registration_cert_path)
WHERE registration_cert_path IS NOT NULL
  AND registration_cert_path != ''
  AND registration_cert_path NOT LIKE 'kyc-documents/%';

UPDATE kyc_verifications
SET pan_cert_path = CONCAT('kyc-documents/', pan_cert_path)
WHERE pan_cert_path IS NOT NULL
  AND pan_cert_path != ''
  AND pan_cert_path NOT LIKE 'kyc-documents/%';

UPDATE kyc_verifications
SET financial_proof_path = CONCAT('kyc-documents/', financial_proof_path)
WHERE financial_proof_path IS NOT NULL
  AND financial_proof_path != ''
  AND financial_proof_path NOT LIKE 'kyc-documents/%';

UPDATE kyc_verifications
SET business_photo_path = CONCAT('kyc-documents/', business_photo_path)
WHERE business_photo_path IS NOT NULL
  AND business_photo_path != ''
  AND business_photo_path NOT LIKE 'kyc-documents/%';
