-- Create test data for payment receipt feature testing
-- This assumes Rosa Owner with ID 3 and Test establishment with ID 1 already exist

-- Check if Test establishment exists
SELECT id, name, owner_id FROM establishment WHERE name = 'Test';

-- Create a test inspection (if not already exists)
INSERT INTO inspection (establishment_id, inspector1, inspection_date, status, payment, notes)
VALUES (1, 2, '2026-05-20 09:00:00', 'completed', 0, 'Test inspection for receipt feature');

-- Get the inspection ID just created
SELECT LAST_INSERT_ID() as inspection_id;

-- Then create a finalized report for it (replace {inspection_id} with the actual ID from above)
-- This is a template - you'll need to run these two steps:
-- 1. First get the inspection_id from the SELECT above
-- 2. Then uncomment and run the INSERT below with the actual ID

-- INSERT INTO reports (inspection_id, compliance_status, finalized_at, finalized_by)
-- VALUES ({inspection_id}, 'compliant', NOW(), 2);

-- INSERT INTO defects (report_id, defects_details, status)
-- VALUES 
-- (LAST_INSERT_ID(), 'Blocked emergency exit - CRITICAL', 'open'),
-- (LAST_INSERT_ID(), 'Fire alarm system not functioning', 'open');
