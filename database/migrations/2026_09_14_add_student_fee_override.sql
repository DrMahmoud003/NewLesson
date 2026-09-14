-- NewLesson V2.4: individual monthly fee imported from Excel
ALTER TABLE students
  ADD COLUMN monthly_fee_override DECIMAL(12,2) NULL AFTER joined_at;
