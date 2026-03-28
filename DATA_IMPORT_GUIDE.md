# JSCA ERP — Data Import Guide

This guide explains how to take official JSCA data (Excel/CSV) and import it into the database correctly.

---

## General Rules

- Always use the **pipe method** to run SQL files — never paste multi-statement SQL directly into `docker exec -e` as it silently swallows errors:
  ```bash
  docker exec -i jsca_erp-db-1 mysql -uroot -proot jsca_erp < your_file.sql
  ```
- Column values marked as **enum** must match exactly — wrong values cause silent truncation or errors.
- `district_id` is always a number — use the District Reference table at the bottom of this file.
- Auto-generated fields (`id`, `created_at`, `updated_at`) — leave these out of your INSERT, the DB handles them.
- `jsca_*_id` fields must be unique. Follow the format shown for each module.

---

## 1. Players

### Excel columns to collect from JSCA
| Excel Column       | DB Column          | Required | Notes |
|--------------------|--------------------|----------|-------|
| Full Name          | `full_name`        | ✅ | Max 100 chars |
| Date of Birth      | `date_of_birth`    | ✅ | Format: `YYYY-MM-DD` |
| Gender             | `gender`           | ✅ | `Male` / `Female` / `Other` |
| District           | `district_id`      | ✅ | Use district ID from reference table below |
| Playing Role       | `role`             | ✅ | See enum values below |
| Batting Style      | `batting_style`    | — | `Right-hand` / `Left-hand` |
| Bowling Style      | `bowling_style`    | — | See enum values below |
| Phone              | `phone`            | — | 10-digit mobile |
| Email              | `email`            | — | |
| Aadhaar Number     | `aadhaar_number`   | — | 12 digits, no spaces |
| Address            | `address`          | — | Free text |
| Guardian Name      | `guardian_name`    | — | For U14/U16 players |
| Guardian Phone     | `guardian_phone`   | — | For U14/U16 players |

### Age Category — auto-derive from DOB
Do NOT ask JSCA for age category — calculate it from DOB:
- Born after `TODAY - 14 years` → `U14`
- Born after `TODAY - 16 years` → `U16`
- Born after `TODAY - 19 years` → `U19`
- Age 19–39 → `Senior`
- Age 40+ → `Masters`

### Enum values
- `role`: `Batsman` | `Bowler` | `All-rounder` | `Wicket-keeper`
- `bowling_style`: `Right-arm Fast` | `Right-arm Medium` | `Right-arm Off-spin` | `Right-arm Leg-spin` | `Left-arm Fast` | `Left-arm Medium` | `Left-arm Orthodox` | `Left-arm Chinaman` | `N/A`
- `status`: `Active` | `Inactive` | `Suspended` | `Retired` — use `Active` for fresh imports
- `selection_pool`: `District` | `State` | `None` — use `None` for fresh imports

### JSCA Player ID format
`JSCA-P-YYYY-NNNNN` — e.g. `JSCA-P-2026-00053`
Check the last used ID first:
```sql
SELECT MAX(jsca_player_id) FROM players;
```

### SQL template
```sql
INSERT INTO players
  (jsca_player_id, full_name, date_of_birth, gender, age_category, district_id,
   role, batting_style, bowling_style, phone, email, aadhaar_number,
   address, guardian_name, guardian_phone, status, registration_type, created_at)
VALUES
  ('JSCA-P-2026-00053', 'Full Name Here', '2005-06-15', 'Male', 'Senior', 1,
   'Batsman', 'Right-hand', 'N/A', '9800000001', NULL, NULL,
   NULL, NULL, NULL, 'Active', 'manual', NOW());

-- Always insert a career stats row for each player
INSERT INTO player_career_stats (player_id)
SELECT id FROM players WHERE jsca_player_id = 'JSCA-P-2026-00053';
```

---

## 2. Coaches

### Excel columns to collect from JSCA
| Excel Column       | DB Column          | Required | Notes |
|--------------------|--------------------|----------|-------|
| Full Name          | `full_name`        | ✅ | |
| Date of Birth      | `date_of_birth`    | ✅ | Format: `YYYY-MM-DD` |
| Gender             | `gender`           | ✅ | `Male` / `Female` / `Other` |
| District           | `district_id`      | ✅ | Use district ID from reference table |
| Phone              | `phone`            | — | |
| Email              | `email`            | — | |
| Specialization     | `specialization`   | ✅ | See enum values below |
| Level / Certification | `level`         | ✅ | See enum values below |
| BCCI Coach ID      | `bcci_coach_id`    | — | If they have one |
| Aadhaar Number     | `aadhaar_number`   | — | 12 digits |
| Experience (years) | `experience_years` | — | Number |
| Previous Teams     | `previous_teams`   | — | Free text |
| Achievements       | `achievements`     | — | Free text |

### Enum values
- `specialization`: `Batting` | `Bowling` | `Fielding` | `Wicket-keeping` | `Fitness` | `General`
- `level`: `Assistant` | `Head Coach` | `Bowling Coach` | `Batting Coach` | `Fielding Coach` | `Fitness Trainer` | `NCA Level 1` | `NCA Level 2` | `NCA Level 3`
- `status`: `Active` | `Inactive` | `Suspended` — use `Active` for fresh imports

### JSCA Coach ID format
`JSCA-C-YYYY-NNNN` — e.g. `JSCA-C-2026-0005`
```sql
SELECT MAX(jsca_coach_id) FROM coaches;
```

### SQL template
```sql
INSERT INTO coaches
  (jsca_coach_id, full_name, date_of_birth, gender, phone, email,
   district_id, specialization, level, bcci_coach_id, aadhaar_number,
   experience_years, previous_teams, achievements, status, registered_by, created_at)
VALUES
  ('JSCA-C-2026-0005', 'Full Name Here', '1985-03-20', 'Male', '9800000002', NULL,
   1, 'Batting', 'NCA Level 2', NULL, NULL,
   10, NULL, NULL, 'Active', 1, NOW());
```

---

## 3. Officials (Umpires, Scorers, Referees)

### Excel columns to collect from JSCA
| Excel Column       | DB Column          | Required | Notes |
|--------------------|--------------------|----------|-------|
| Full Name          | `full_name`        | ✅ | |
| Official Type      | `official_type_id` | ✅ | See type IDs below |
| Date of Birth      | `dob`              | — | Format: `YYYY-MM-DD` |
| Gender             | `gender`           | ✅ | `Male` / `Female` / `Other` |
| District           | `district_id`      | ✅ | Use district ID from reference table |
| Phone              | `phone`            | — | |
| Email              | `email`            | — | |
| Grade              | `grade`            | — | See grade values by type below |
| Experience (years) | `experience_years` | — | Number |
| Fee Per Match      | `fee_per_match`    | — | Decimal e.g. `500.00` |
| Bank Name          | `bank_name`        | — | For payment processing |
| Bank Account       | `bank_account`     | — | |
| Bank IFSC          | `bank_ifsc`        | — | 11 chars |
| Address            | `address`          | — | |

### Tournament Excel column mapping
| Excel Column | DB Column | Notes |
|-------------|-----------|-------|
| Name | `name` | Tournament full name |
| Format | `format` | `T10` / `T20` / `ODI-40` / `ODI-50` / `Test` / `Custom` |
| Gender | `gender` | `Male` / `Female` / `Mixed` |
| Category | `age_category` | See mapping below |
| Level | `type` | `District` / `State` / `National` / `Invitational` |

### Category mapping (JSCA Excel → DB)
| JSCA Excel value | DB `age_category` |
|-----------------|-------------------|
| OPEN / Open | `Open` |
| U-23 / U23 | `U23` |
| U-19 / U19 | `U19` |
| U-16 / U16 | `U16` |
| U-15 / U15 | `U15` |
| U-14 / U14 | `U14` |
| Women / Women's | `Women` |
| Senior | `Senior` |

### Level mapping (JSCA Excel → DB `type` column)
| JSCA Excel value | DB `type` value |
|-----------------|------------------|
| Club | `Club` |
| District | `District` |
| State | `State` |
| National | `National` |
| Invitational | `Invitational` |

### Grade values by official type

JSCA uses different column names in their Excel depending on official type — both map to the `grade` column in our DB:

**Umpires** — JSCA calls this column `level`:
| JSCA Excel value | DB `grade` value |
|-----------------|------------------|
| Elite / BCCI Elite | `Elite Panel` |
| BCCI Level-1 / BCCI Level 1 | `BCCI` |
| Ranji / Ranji Panel | `Ranji` |
| Grade-I / Grade I | `Grade I` |
| Grade-II / Grade II | `Grade II` |
| State Panel | `State Panel` |

**Scorers & Referees** — JSCA calls this column `grade`:
| JSCA Excel value | DB `grade` value |
|-----------------|------------------|
| BCCI Panel / BCCI PANEL | `BCCI` |
| State Panel / STATE PANEL | `State Panel` |

### JSCA Official ID format
The ID is **type-specific** — each official type has its own prefix and its own counter:

| Type | Format | Example |
|------|--------|---------|
| Umpire | `JSCA-UMP-NNNN` | `JSCA-UMP-0001` |
| Scorer | `JSCA-SCR-NNNN` | `JSCA-SCR-0001` |
| Referee | `JSCA-REF-NNNN` | `JSCA-REF-0001` |
| Match Referee | `JSCA-MRF-NNNN` | `JSCA-MRF-0001` |

The counter resets per type — so `JSCA-UMP-0001` and `JSCA-SCR-0001` can both exist.

Check the last used ID per type before importing:
```sql
SELECT ot.name, ot.prefix, MAX(o.jsca_official_id) as last_id
FROM officials o
JOIN official_types ot ON ot.id = o.official_type_id
GROUP BY ot.id;
```

### SQL template
```sql
-- Umpire example
INSERT INTO officials
  (jsca_official_id, official_type_id, full_name, dob, gender,
   district_id, phone, email, grade, experience_years,
   fee_per_match, bank_name, bank_account, bank_ifsc, address, status, registered_by, created_at)
VALUES
  ('JSCA-UMP-0001', 1, 'Umpire Name', '1980-07-10', 'Male',
   1, '9800000003', NULL, 'B', 8,
   500.00, NULL, NULL, NULL, NULL, 'Active', 1, NOW());

-- Scorer example
INSERT INTO officials
  (jsca_official_id, official_type_id, full_name, dob, gender,
   district_id, phone, email, grade, experience_years,
   fee_per_match, bank_name, bank_account, bank_ifsc, address, status, registered_by, created_at)
VALUES
  ('JSCA-SCR-0001', 2, 'Scorer Name', '1985-03-15', 'Male',
   1, '9800000004', NULL, 'State Panel', 5,
   300.00, NULL, NULL, NULL, NULL, 'Active', 1, NOW());
```

---

## 4. Venues

### Excel columns to collect from JSCA
| Excel Column       | DB Column          | Required | Notes |
|--------------------|--------------------|----------|-------|
| Venue Name         | `name`             | ✅ | Max 150 chars |
| District           | `district_id`      | ✅ | Use district ID from reference table |
| Capacity           | `capacity`         | — | Number of spectators |
| Has Floodlights    | `has_floodlights`  | — | `1` = Yes, `0` = No |
| Has Scoreboard     | `has_scoreboard`   | — | `1` = Yes, `0` = No |
| Has Dressing Room  | `has_dressing`     | — | `1` = Yes, `0` = No |
| Pitch Type         | `pitch_type`       | — | See enum values below |
| Contact Person     | `contact_person`   | — | |
| Contact Phone      | `contact_phone`    | — | 10-digit mobile |
| Address            | `address`          | — | |
| Latitude           | `lat`              | — | Decimal e.g. `23.344700` |
| Longitude          | `lng`              | — | Decimal e.g. `85.309562` |

### Enum values
- `pitch_type`: `Grass` | `Turf` | `Concrete` | `Red-soil`

### SQL template
```sql
INSERT INTO venues
  (name, district_id, capacity, has_floodlights, has_scoreboard, has_dressing,
   pitch_type, contact_person, contact_phone, address, lat, lng, is_active, created_at)
VALUES
  ('JSCA Stadium Ground 2', 1, 5000, 1, 1, 1,
   'Grass', 'Contact Name', '9800000004', 'Sector 5, Ranchi', 23.344700, 85.309562, 1, NOW());
```

---

## 5. Bulk Import Workflow (Excel → SQL)

When you receive an Excel file from JSCA:

**Step 1 — Clean the Excel**
- Remove merged cells, header rows, blank rows
- Standardize date format to `YYYY-MM-DD`
- Map district names to IDs using the reference table below
- Map enum text to exact allowed values (e.g. "Right Hand Bat" → `Right-hand`)

**Step 2 — Convert to SQL**
You can use Excel formulas to build INSERT rows. Example for a player row in Excel:
```
="('JSCA-P-2026-"&TEXT(ROW()-1,"00000")&"', '"&A2&"', '"&TEXT(B2,"YYYY-MM-DD")&"', '"&C2&"', 'Senior', "&D2&", '"&E2&"', '"&F2&"', 'N/A', '"&G2&"', NULL, NULL, NULL, NULL, NULL, 'Active', 'manual', NOW()),"
```

**Step 3 — Wrap in INSERT and save as .sql file**
```sql
INSERT INTO players (jsca_player_id, full_name, ...) VALUES
(...row 1...),
(...row 2...),
(...row N...);
```

**Step 4 — Run via pipe (never -e for multi-row)**
```bash
docker exec -i jsca_erp-db-1 mysql -uroot -proot jsca_erp < import_players.sql
```

**Step 5 — Verify**
```sql
SELECT COUNT(*) FROM players WHERE created_at >= CURDATE();
```

---

## District Reference Table

| ID | District       | Zone    | Code |
|----|----------------|---------|------|
| 1  | Ranchi         | South   | RCH  |
| 2  | Dhanbad        | East    | DHN  |
| 3  | Bokaro         | East    | BKR  |
| 4  | Jamshedpur     | East    | JMP  |
| 5  | Hazaribagh     | North   | HZB  |
| 6  | Giridih        | North   | GRD  |
| 7  | Deoghar        | West    | DGR  |
| 8  | Dumka          | West    | DMK  |
| 9  | Chatra         | North   | CHT  |
| 10 | Koderma        | North   | KDR  |
| 11 | Lohardaga      | South   | LHD  |
| 12 | Gumla          | South   | GML  |
| 13 | Simdega        | South   | SMD  |
| 14 | Pakur          | West    | PKR  |
| 15 | Godda          | West    | GDA  |
| 16 | Sahebganj      | West    | SHB  |
| 17 | Jamtara        | West    | JMT  |
| 18 | Palamu         | Central | PLM  |
| 19 | Garhwa         | Central | GRW  |
| 20 | Latehar        | Central | LTR  |
| 21 | Khunti         | South   | KHT  |
| 22 | West Singhbhum | East    | WSB  |
| 23 | Seraikela      | East    | SKL  |
| 24 | Ramgarh        | North   | RMG  |

---

## Common Mistakes to Avoid

| Mistake | What happens | Fix |
|---------|-------------|-----|
| Using `docker exec -e` for multi-row inserts | Errors are silently swallowed, rows not inserted | Always use `< file.sql` pipe |
| Wrong enum value e.g. `"Level 2"` instead of `"NCA Level 2"` | Data truncated error or silent failure | Copy exact values from this guide |
| Forgetting `player_career_stats` insert after players | Player profile page crashes | Always add the career stats INSERT |
| Date format `DD/MM/YYYY` from Excel | Invalid date, insert fails | Convert to `YYYY-MM-DD` first |
| Duplicate `jsca_*_id` | Unique key violation, whole batch fails | Check `MAX(jsca_player_id)` before starting |
| Missing `district_id` | Foreign key error | Always map district name → ID using table above |

---

## Foreign Key Map — Table Relationships

Understanding this prevents FK errors when inserting or deleting data.

```
districts
    ├── players.district_id
    ├── coaches.district_id
    ├── officials.district_id
    ├── venues.district_id
    ├── teams.district_id
    └── user_districts.district_id

players
    ├── player_career_stats.player_id   ← always insert this after every player
    ├── player_documents.player_id
    ├── team_players.player_id
    ├── teams.captain_id
    ├── teams.vice_captain_id
    ├── batting_stats.player_id
    └── bowling_stats.player_id

coaches
    ├── coach_documents.coach_id
    └── team_coaches.coach_id

officials
    ├── official_certifications.official_id
    ├── fixtures.umpire1_id
    ├── fixtures.umpire2_id
    ├── fixtures.scorer_id
    ├── fixtures.referee_id
    └── payment_vouchers.official_id

venues
    └── fixtures.venue_id

tournaments
    ├── teams.tournament_id
    ├── fixtures.tournament_id
    ├── tournament_documents.tournament_id
    ├── tournament_budgets.tournament_id
    └── payment_vouchers.tournament_id

teams
    ├── team_players.team_id
    ├── team_coaches.team_id
    ├── team_documents.team_id
    ├── fixtures.team_a_id
    ├── fixtures.team_b_id
    ├── batting_stats.team_id
    ├── bowling_stats.team_id
    └── live_matches.team_a_id / team_b_id

fixtures
    ├── batting_stats.fixture_id
    ├── bowling_stats.fixture_id
    ├── match_scorecards.fixture_id
    └── payment_vouchers.fixture_id

roles
    ├── users.role_id
    └── official_types.role_id

users
    ├── players.registered_by
    ├── officials.registered_by
    ├── officials.user_id
    ├── tournaments.created_by
    ├── payment_vouchers.created_by
    ├── payment_vouchers.approved_by
    └── user_districts.user_id
```

---

## Safe Deletion Order

**Golden rule: always delete children before parents.**

### Clear everything except players
```sql
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE batting_stats;
TRUNCATE TABLE bowling_stats;
TRUNCATE TABLE match_scorecards;
TRUNCATE TABLE payment_vouchers;
TRUNCATE TABLE fixtures;
TRUNCATE TABLE live_matches;
TRUNCATE TABLE team_players;
TRUNCATE TABLE team_coaches;
TRUNCATE TABLE team_documents;
TRUNCATE TABLE teams;
TRUNCATE TABLE tournament_documents;
TRUNCATE TABLE tournament_budgets;
TRUNCATE TABLE tournaments;
TRUNCATE TABLE official_certifications;
TRUNCATE TABLE officials;
TRUNCATE TABLE coach_documents;
TRUNCATE TABLE coaches;
TRUNCATE TABLE venues;
SET FOREIGN_KEY_CHECKS = 1;
```

### Delete a single tournament safely
```sql
SET FOREIGN_KEY_CHECKS = 0;
DELETE bs FROM batting_stats bs JOIN fixtures f ON f.id=bs.fixture_id WHERE f.tournament_id=?;
DELETE bs FROM bowling_stats bs JOIN fixtures f ON f.id=bs.fixture_id WHERE f.tournament_id=?;
DELETE FROM fixtures WHERE tournament_id=?;
UPDATE teams SET captain_id=NULL, vice_captain_id=NULL WHERE tournament_id=?;
DELETE FROM team_players WHERE team_id IN (SELECT id FROM teams WHERE tournament_id=?);
DELETE FROM team_coaches WHERE team_id IN (SELECT id FROM teams WHERE tournament_id=?);
DELETE FROM team_documents WHERE team_id IN (SELECT id FROM teams WHERE tournament_id=?);
DELETE FROM teams WHERE tournament_id=?;
DELETE FROM tournament_documents WHERE tournament_id=?;
DELETE FROM tournaments WHERE id=?;
SET FOREIGN_KEY_CHECKS = 1;
```

### Delete a single player safely
```sql
-- Check for match stats first — if any exist, do NOT delete (historical record)
SELECT COUNT(*) FROM batting_stats WHERE player_id=?;
SELECT COUNT(*) FROM bowling_stats WHERE player_id=?;

-- Only proceed if both return 0:
DELETE FROM team_players WHERE player_id=?;
DELETE FROM player_documents WHERE player_id=?;
DELETE FROM player_career_stats WHERE player_id=?;
UPDATE teams SET captain_id=NULL WHERE captain_id=?;
UPDATE teams SET vice_captain_id=NULL WHERE vice_captain_id=?;
DELETE FROM players WHERE id=?;
```

### Delete a single official safely
```sql
UPDATE fixtures SET umpire1_id=NULL WHERE umpire1_id=?;
UPDATE fixtures SET umpire2_id=NULL WHERE umpire2_id=?;
UPDATE fixtures SET scorer_id=NULL WHERE scorer_id=?;
UPDATE fixtures SET referee_id=NULL WHERE referee_id=?;
DELETE FROM official_certifications WHERE official_id=?;
DELETE FROM officials WHERE id=?;
```

### Delete a single venue safely
```sql
UPDATE fixtures SET venue_id=NULL WHERE venue_id=?;
DELETE FROM venues WHERE id=?;
```

---

## TRUNCATE vs DELETE

| | TRUNCATE | DELETE |
|---|---|---|
| Resets auto-increment ID | ✅ Yes | ❌ No |
| Can use WHERE clause | ❌ No | ✅ Yes |
| Faster on large tables | ✅ Yes | ❌ No |
| Respects FK checks | ❌ No (need to disable) | ✅ Yes |

Use `TRUNCATE` when clearing entire tables for a fresh start.
Use `DELETE WHERE` when removing specific records.

Always wrap TRUNCATE operations with:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- your truncates here
SET FOREIGN_KEY_CHECKS = 1;
```
