-- Create a test inspection
INSERT INTO inspection (establishment_id, inspector1, inspector2, inspection_date, inspection_type, status, payment) 
VALUES (1, 2, 3, '2026-05-20 09:00:00', 'routine', 'completed', 0);

-- Get the inspection ID just created  
SET @inspection_id = LAST_INSERT_ID();

-- Create a finalized report for this inspection
INSERT INTO reports (inspection_id, compliance_status, finalized_at) 
VALUES (@inspection_id, 'compliant', NOW());

-- Get report ID
SET @report_id = LAST_INSERT_ID();

-- Add test defects
INSERT INTO defects (report_id, defects_details, grace_period, status) 
VALUES 
(@report_id, 'Blocked emergency exit - CRITICAL', '30 days', 'open'),
(@report_id, 'Fire alarm system not functioning', '15 days', 'open');
