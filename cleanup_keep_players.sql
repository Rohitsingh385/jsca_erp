-- ============================================================
-- JSCA ERP — Safe Data Cleanup
-- Clears: venues, coaches, officials, tournaments, teams, fixtures
-- Keeps:  players, player_documents, player_career_stats
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Stats & scorecards tied to fixtures
TRUNCATE TABLE batting_stats;
TRUNCATE TABLE bowling_stats;
TRUNCATE TABLE match_scorecards;

-- 2. Fixtures and related
TRUNCATE TABLE payment_vouchers;
TRUNCATE TABLE fixtures;
TRUNCATE TABLE live_matches;

-- 3. Team relationships
TRUNCATE TABLE team_players;
TRUNCATE TABLE team_coaches;
TRUNCATE TABLE team_documents;

-- 4. Teams
TRUNCATE TABLE teams;

-- 5. Tournament related
TRUNCATE TABLE tournament_documents;
TRUNCATE TABLE tournament_budgets;
TRUNCATE TABLE tournaments;

-- 6. Officials
TRUNCATE TABLE official_certifications;
TRUNCATE TABLE officials;

-- 7. Coaches
TRUNCATE TABLE coach_documents;
TRUNCATE TABLE coaches;

-- 8. Venues
TRUNCATE TABLE venues;

-- 9. Clean up users created for officials/coaches
--    Keep: superadmin (id=1), admin users, and player-linked users
--    Delete: users linked to officials/coaches (role: umpire, scorer, referee, match_referee)
DELETE FROM users WHERE role_id IN (
    SELECT id FROM roles WHERE name IN ('umpire','scorer','referee','match_referee')
);

SET FOREIGN_KEY_CHECKS = 1;

-- Verify what's left
SELECT 'players' as tbl, COUNT(*) as remaining FROM players
UNION ALL SELECT 'player_career_stats', COUNT(*) FROM player_career_stats
UNION ALL SELECT 'player_documents', COUNT(*) FROM player_documents
UNION ALL SELECT 'coaches', COUNT(*) FROM coaches
UNION ALL SELECT 'officials', COUNT(*) FROM officials
UNION ALL SELECT 'venues', COUNT(*) FROM venues
UNION ALL SELECT 'tournaments', COUNT(*) FROM tournaments
UNION ALL SELECT 'teams', COUNT(*) FROM teams
UNION ALL SELECT 'fixtures', COUNT(*) FROM fixtures;
