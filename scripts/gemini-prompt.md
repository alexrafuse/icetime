# Gemini Prompt: Convert Calendar JSON to CSV

## Instructions

Copy the contents of `calendar_filtered_2025_2026.json` and paste it along with this prompt to Gemini (or Claude/ChatGPT).

---

## Prompt to Use

```
I have calendar booking data in JSON format that I need to convert to CSV for database seeding. Please convert this JSON data into a clean CSV file with the following specifications:

**CSV Format - CRITICAL:**
The CSV MUST have EXACTLY 6 columns with this header:
date,title,start_time,end_time,notes,sheet_count

**Column Specifications:**
1. date - YYYY-MM-DD format (required, never empty)
2. title - The entry name from the JSON (required, never empty)
3. start_time - HH:MM in 24-hour format (required, NEVER empty - see defaults below)
4. end_time - HH:MM in 24-hour format (required, NEVER empty - see defaults below)
5. notes - Any additional info from comments (can be empty, use empty string not blank)
6. sheet_count - Just the number (1, 2, 4, etc.) or empty if not mentioned

**CRITICAL RULES:**
1. EVERY row MUST have start_time and end_time - NEVER leave these empty
2. EXACTLY 6 columns per row (including header) - no more, no less
3. Times MUST be HH:MM format (not HH:MM:SS)
4. If a day has multiple entries in the "entries" array, create a SEPARATE CSV row for EACH entry
5. Keep titles exactly as they appear (including typos)

**Time Parsing Rules:**
1. Extract time ranges from the comments field
2. Convert all times to 24-hour HH:MM format
3. **DEFAULT TIMES (when no time info in comments):**
   - Tournaments/Competitions/Games/Spiel: 09:00,17:00
   - Practices/Clinics: 18:00,21:00
   - Workshops/Courses: 09:00,15:00
   - Parties/Events: 18:00,22:00
   - If "All Day" mentioned explicitly: 00:00,23:59
   - All other events with no time: 09:00,17:00
4. For sheet_count: Extract ONLY the number (if "1 Sheet" → put "1" in column, if "4 sheets" → put "4")
5. For notes column: Keep original comment text, but if it contains commas, wrap in quotes

**Example Transformations:**

Input JSON:
{
  "date": "2025-01-03",
  "entries": ["Densmore Pracice"],
  "comments": ["User:\n5-7PM"]
}
Output CSV row:
2025-01-03,Densmore Pracice,17:00,19:00,5-7PM,

Input JSON:
{
  "date": "2025-01-06",
  "entries": ["SO Practice"],
  "comments": ["User:\n6:30-8:00PM - 1 Sheet"]
}
Output CSV row:
2025-01-06,SO Practice,18:30,20:00,6:30-8:00PM - 1 Sheet,1

Input JSON:
{
  "date": "2025-01-30",
  "entries": ["Firefighters"],
  "comments": ["User:\nAll Day Event"]
}
Output CSV row:
2025-01-30,Firefighters,00:00,23:59,All Day Event,

Input JSON (NO TIME INFO - must default):
{
  "date": "2025-01-25",
  "entries": ["Junior Practice"],
  "comments": null
}
Output CSV row:
2025-01-25,Junior Practice,18:00,21:00,,

Input JSON (multiple entries on same day):
{
  "date": "2025-02-02",
  "entries": ["Firefighters", "SO Practice"],
  "comments": ["User:\nAll Day Event", "User:\n6:30-8:00PM - 1 Sheet"]
}
Output CSV rows (TWO separate rows):
2025-02-02,Firefighters,00:00,23:59,All Day Event,
2025-02-02,SO Practice,18:30,20:00,6:30-8:00PM - 1 Sheet,1

**What NOT to do (common errors):**
❌ DON'T leave start_time or end_time empty: 2025-01-17,Mixed Prov Stick,,,,
✅ DO provide default times: 2025-01-17,Mixed Prov Stick,09:00,17:00,,

❌ DON'T add extra columns: 2025-01-23,Title,09:00,17:00,notes,,"extra column"
✅ DO keep exactly 6 columns: 2025-01-23,Title,09:00,17:00,notes,

❌ DON'T use 12-hour format: 2025-01-03,Title,5:00PM,7:00PM,,
✅ DO use 24-hour HH:MM: 2025-01-03,Title,17:00,19:00,,

❌ DON'T put text in sheet_count: 2025-01-06,Title,18:00,20:00,,1 Sheet
✅ DO put only the number: 2025-01-06,Title,18:00,20:00,,1

**Final Validation:**
Before returning the CSV, verify:
- Every row has exactly 6 comma-separated values
- No row has empty start_time or end_time
- All times are in HH:MM format (5 characters: 2 digits, colon, 2 digits)
- Header row is: date,title,start_time,end_time,notes,sheet_count

Please process the following JSON data:
