-- Fix nutritionist status workflow
-- This will show existing nutritionists and allow you to activate them

-- First, let's see what nutritionists exist and their current status
-- (Run this first to see what you have)

-- For lowercase table names:
SELECT 
    n.nutritionist_id,
    u.name,
    u.email,
    n.status,
    n.created_at
FROM nutritionist n
JOIN user_table u ON n.nutritionist_id = u.user_id
ORDER BY n.created_at DESC;

-- If the above doesn't work, try uppercase:
-- SELECT 
--     n.nutritionist_id,
--     u.name,
--     u.email,
--     n.status,
--     n.created_at
-- FROM Nutritionist n
-- JOIN User_Table u ON n.nutritionist_id = u.user_id
-- ORDER BY n.created_at DESC;

-- ========================================
-- ACTIVATE ALL PENDING NUTRITIONISTS
-- ========================================

-- For lowercase table names:
UPDATE nutritionist 
SET status = 'active', updated_at = NOW() 
WHERE status = 'pending';

-- If the above doesn't work, try uppercase:
-- UPDATE Nutritionist 
-- SET status = 'active', updated_at = NOW() 
-- WHERE status = 'pending';

-- ========================================
-- VERIFY THE CHANGES
-- ========================================

-- Check the results (lowercase):
SELECT 
    n.nutritionist_id,
    u.name,
    u.email,
    n.status,
    n.specialization,
    COALESCE(n.experience_years, n.experience, 0) as experience
FROM nutritionist n
JOIN user_table u ON n.nutritionist_id = u.user_id
ORDER BY n.created_at DESC;

-- If needed, try uppercase:
-- SELECT 
--     n.nutritionist_id,
--     u.name,
--     u.email,
--     n.status,
--     n.specialization,
--     COALESCE(n.experience_years, n.experience, 0) as experience
-- FROM Nutritionist n
-- JOIN User_Table u ON n.nutritionist_id = u.user_id
-- ORDER BY n.created_at DESC;