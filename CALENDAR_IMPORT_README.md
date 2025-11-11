# Calendar Import Guide

This guide walks you through importing historical calendar data from Excel/JSON into your IceTime booking system.

## Overview

The import process has 3 phases:
1. **Filter** the large JSON file to only 2025+ dates with events
2. **Preprocess** with Gemini AI to clean and format time data into CSV
3. **Seed** the database with the cleaned CSV data

---

## Phase 1: Filter the JSON Data

### Prerequisites
- Node.js installed
- `book2_calendar_2025_2026_days.json` in project root

### Steps

```bash
# Run the filtering script
node scripts/filter-calendar-data.js
```

This will:
- Read `book2_calendar_2025_2026_days.json` (732 days total)
- Filter to only days from 2025 onwards
- Filter to only days with at least one entry (skip empty days)
- Output to `calendar_filtered_2025_2026.json`

**Expected Output:**
```
📅 Calendar Data Filter
==================================================
Reading: /Users/alex/Projects/icetime/book2_calendar_2025_2026_days.json
Total days in source: 732
✅ Filtering complete!
Days with entries (2025+): 156
Reduction: 576 empty/old days removed
Output saved to: /Users/alex/Projects/icetime/calendar_filtered_2025_2026.json
```

---

## Phase 2: AI Preprocessing with Gemini

### Why AI Preprocessing?

The calendar data has inconsistent time formats in comments:
- `"5-7PM"` → needs parsing to `17:00` and `19:00`
- `"6:30-8:00PM - 1 Sheet"` → needs time + sheet extraction
- `"All Day Event"` → needs conversion to `00:00-23:59`

Gemini AI excels at parsing these natural language variations.

### Steps

1. **Open the prompt template:**
   ```bash
   cat scripts/gemini-prompt.md
   ```

2. **Copy the filtered JSON:**
   ```bash
   cat calendar_filtered_2025_2026.json | pbcopy  # macOS
   # or manually copy the file contents
   ```

3. **Go to Gemini** (https://gemini.google.com or https://aistudio.google.com)

4. **Paste the prompt** from `scripts/gemini-prompt.md`

5. **Paste the JSON data** where indicated in the prompt

6. **Copy Gemini's CSV output**

7. **Save to file:**
   ```bash
   # Create the file and paste Gemini's CSV output
   nano database/seeders/data/calendar_import_2025_2026.csv
   # or use your editor of choice
   ```

### Expected CSV Format

```csv
date,title,start_time,end_time,notes,sheet_count
2025-01-03,Densmore Pracice,17:00,19:00,5-7PM,
2025-01-04,Bruce Densmore,09:00,17:00,Stick Leauge competition,
2025-01-06,SO Practice,18:30,20:00,1 Sheet,1
2025-01-12,Elsa Tokunaga,14:00,16:00,Shed - 8 people,
2025-01-30,Firefighters,00:00,23:59,All Day Event,
...
```

---

## Phase 3: Run the Seeder

### Prerequisites

Make sure you've run the base seeders first:
```bash
php artisan migrate:fresh --seed
```

This creates:
- Admin user (alex@example.com)
- 4 ice sheets (Sheet A, B, C, D)
- Availabilities for each sheet

### Import the Calendar Data

```bash
php artisan db:seed --class=CalendarImportSeeder
```

**Expected Output:**
```
📅 Starting Calendar Import...
Reading CSV: /path/to/database/seeders/data/calendar_import_2025_2026.csv
Imported 10 bookings...
Imported 20 bookings...
...
Imported 150 bookings...

✅ Calendar Import Complete!
Successfully imported: 156 bookings
```

### Verify in Filament

1. Navigate to: http://localhost:8000/admin
2. Login with admin credentials (check DatabaseSeeder.php)
3. Go to "Bookings" or "Calendar" view
4. You should see all imported events from 2025+

---

## Rollback / Cleanup

If you need to remove imported bookings:

```bash
# Option 1: Delete all PRIVATE bookings (imported ones)
php artisan tinker
>>> \App\Models\Booking::where('event_type', 'private')->delete();

# Option 2: Fresh migration (CAREFUL: deletes ALL data)
php artisan migrate:fresh --seed
```

---

## Troubleshooting

### Error: "CSV file not found"

Make sure you've completed Phase 2 and saved the CSV to:
```
database/seeders/data/calendar_import_2025_2026.csv
```

### Error: "No users found in database"

Run the base seeders first:
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Error: "No ice sheets found"

Run the area seeder:
```bash
php artisan db:seed --class=AreaAndAvailabilitySeeder
```

### Skipped rows during import

Check the error messages in the seeder output. Common issues:
- Invalid date format
- Missing required fields (date, title, start_time, end_time)
- Malformed CSV (extra commas, missing quotes)

### Times are wrong

Double-check the CSV has 24-hour format (HH:MM):
- ✅ Good: `18:30`, `09:00`, `23:59`
- ❌ Bad: `6:30PM`, `9am`, `11:59:59pm`

---

## Data Mapping Reference

### EventType
All imported bookings use: `EventType::PRIVATE`

To change after import:
```bash
php artisan tinker
>>> \App\Models\Booking::where('title', 'SO Practice')->update(['event_type' => 'league']);
```

### PaymentStatus
All imported bookings use: `PaymentStatus::PENDING`

### Area Assignment
- If `sheet_count` = 1 → assigns Sheet A
- If `sheet_count` = 2 → assigns Sheet A + B
- If `sheet_count` = 4 → assigns Sheet A + B + C + D
- If not specified → defaults to Sheet A

---

## File Structure

```
icetime/
├── book2_calendar_2025_2026_days.json      # Original (732 days)
├── calendar_filtered_2025_2026.json        # Filtered (2025+, events only)
├── scripts/
│   ├── filter-calendar-data.js             # Phase 1 script
│   └── gemini-prompt.md                    # Phase 2 instructions
├── database/
│   └── seeders/
│       ├── CalendarImportSeeder.php        # Phase 3 seeder
│       └── data/
│           └── calendar_import_2025_2026.csv  # Gemini output (you create)
└── CALENDAR_IMPORT_README.md               # This file
```

---

## Questions?

- Check the seeder code: `database/seeders/CalendarImportSeeder.php`
- Review the Gemini prompt: `scripts/gemini-prompt.md`
- See existing seeders for patterns: `database/seeders/LeagueSeeder.php`

Happy importing! 🎉
