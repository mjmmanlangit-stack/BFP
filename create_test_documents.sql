-- Create test document records for establishment 12 (Test) with owner 6 (Rosa Owner)
INSERT INTO documents (establishment_id, owner_id, document_type, filename, original_name, file_size, status, createdAt) 
VALUES 
(12, 6, 'Fire Safety Evaluation Clearance', 'doc_6_1_1780064376.png', 'Fire-Safety-Clearance.png', 2048000, 'approved', NOW()),
(12, 6, 'Occupancy Permit', 'doc_6_1_1780063802.png', 'Occupancy-Permit.png', 1536000, 'approved', NOW()),
(12, 6, 'Business Permit', 'doc_6_1_1780064467.png', 'Business-Permit.png', 1800000, 'pending', NOW()),
(12, 6, 'Valid ID', 'doc_6_1_1780064039.docx', 'Valid-ID.docx', 512000, 'approved', NOW()),
(12, 6, 'Building Plans/Floor Plan', 'doc_6_1_1780065065.docx', 'Floor-Plans.docx', 3145728, 'pending', NOW());
