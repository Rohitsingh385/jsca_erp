UPDATE officials SET grade = NULL;
ALTER TABLE officials MODIFY COLUMN grade ENUM('Ranji','BCCI','Elite Panel','Grade I','Grade II','State Panel') NULL;
UPDATE officials SET grade = 'BCCI' WHERE official_type_id = 2 AND jsca_official_id LIKE 'JSCA-SCR-%' AND jsca_official_id IN ('JSCA-SCR-0001','JSCA-SCR-0002','JSCA-SCR-0003','JSCA-SCR-0004');
UPDATE officials SET grade = 'State Panel' WHERE official_type_id = 2 AND jsca_official_id NOT IN ('JSCA-SCR-0001','JSCA-SCR-0002','JSCA-SCR-0003','JSCA-SCR-0004');
