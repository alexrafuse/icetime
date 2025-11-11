#!/usr/bin/env node

/**
 * Filter calendar JSON data to only include:
 * - Days from 2025 onwards
 * - Days with at least one entry (non-empty)
 *
 * Usage: node scripts/filter-calendar-data.js
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// File paths
const INPUT_FILE = path.join(__dirname, '..', 'book2_calendar_2025_2026_days.json');
const OUTPUT_FILE = path.join(__dirname, '..', 'calendar_filtered_2025_2026.json');

console.log('📅 Calendar Data Filter');
console.log('='.repeat(50));

// Read the input JSON file
console.log(`Reading: ${INPUT_FILE}`);
const rawData = fs.readFileSync(INPUT_FILE, 'utf8');
const data = JSON.parse(rawData);

console.log(`Total days in source: ${data.days_extracted}`);

// Filter the days
const filteredDays = data.days.filter(day => {
    // Filter 1: Year must be 2025 or later
    const year = parseInt(day.year);
    if (year < 2025) {
        return false;
    }

    // Filter 2: Must have at least one entry
    if (!day.entries || day.entries.length === 0) {
        return false;
    }

    return true;
});

// Create filtered output object
const filteredData = {
    source_file: data.workbook,
    original_generated_at: data.generated_at,
    filtered_at: new Date().toISOString(),
    filter_criteria: {
        min_year: 2025,
        require_entries: true
    },
    original_days_count: data.days_extracted,
    filtered_days_count: filteredDays.length,
    days: filteredDays
};

// Write to output file
fs.writeFileSync(OUTPUT_FILE, JSON.stringify(filteredData, null, 2), 'utf8');

console.log('✅ Filtering complete!');
console.log(`Days with entries (2025+): ${filteredDays.length}`);
console.log(`Reduction: ${data.days_extracted - filteredDays.length} empty/old days removed`);
console.log(`Output saved to: ${OUTPUT_FILE}`);
console.log('='.repeat(50));

// Print some sample entries for verification
console.log('\n📋 Sample filtered entries (first 5):');
filteredDays.slice(0, 5).forEach((day, idx) => {
    console.log(`\n${idx + 1}. ${day.date} - ${day.entries.join(', ')}`);
    if (day.comments && day.comments.length > 0) {
        console.log(`   Comment: ${day.comments[0].replace(/\n/g, ' ')}`);
    }
});

console.log('\n✨ Ready for Gemini processing!');
console.log('Next step: Copy the filtered JSON to Gemini with the provided prompt.');
