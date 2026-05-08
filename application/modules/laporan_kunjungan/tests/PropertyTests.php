<?php
/**
 * Property-Based Tests for Laporan Kunjungan Konsultan Module
 *
 * Self-contained PHP script that tests pure logic functions extracted from the controller.
 * Run with: php PropertyTests.php
 *
 * Each property is tested with 100 random iterations.
 * No database connection needed — tests pure logic only.
 */

// ============================================================================
// PURE LOGIC FUNCTIONS (extracted from controller for testability)
// ============================================================================

/**
 * Calculate mandays_used from duration in minutes.
 * Formula: ROUND(duration_minutes / 60 / 8, 2)
 *
 * @param int $start_timestamp Unix timestamp of start time
 * @param int $finish_timestamp Unix timestamp of finish time
 * @return float Mandays used, rounded to 2 decimal places
 */
function calculate_mandays($start_timestamp, $finish_timestamp)
{
    $duration_minutes = ($finish_timestamp - $start_timestamp) / 60;
    return round($duration_minutes / 60 / 8, 2);
}

/**
 * Calculate cumulative mandays from an array of individual visit mandays values.
 *
 * @param array $mandays_values Array of float mandays values
 * @return float Sum of all mandays values
 */
function calculate_cumulative_mandays($mandays_values)
{
    return array_sum($mandays_values);
}

/**
 * Calculate sisa (remaining) mandays.
 *
 * @param float $allocated Total mandays allocated
 * @param float $used Total mandays used
 * @return float Remaining mandays
 */
function calculate_sisa_mandays($allocated, $used)
{
    return $allocated - $used;
}

/**
 * Validate kegiatan name — rejects empty or whitespace-only strings.
 *
 * @param string $kegiatan The kegiatan name to validate
 * @return bool True if valid (non-empty after trim), false if invalid
 */
function validate_kegiatan($kegiatan)
{
    return trim($kegiatan) !== '';
}

/**
 * Validate action plan due date — must be >= visit_date.
 *
 * @param string $due_date Due date in Y-m-d format
 * @param string $visit_date Visit date in Y-m-d format
 * @return bool True if valid (due_date >= visit_date), false otherwise
 */
function validate_due_date($due_date, $visit_date)
{
    return $due_date >= $visit_date;
}

/**
 * Simulate draft save round-trip using JSON encode/decode.
 * Verifies all fields are preserved through serialization.
 *
 * @param array $data Visit data structure
 * @return array Decoded data after round-trip
 */
function draft_save_roundtrip($data)
{
    $encoded = json_encode($data);
    return json_decode($encoded, true);
}

/**
 * Validate all required fields for final save.
 * Returns true if all required fields are valid, false otherwise.
 *
 * @param array $input The visit report input data
 * @return bool True if validation passes, false if it fails
 */
function validate_final_save($input)
{
    // Validate start_time
    $start_time = isset($input['start_time']) ? trim($input['start_time']) : '';
    if (empty($start_time)) {
        return false;
    }

    // Validate finish_time
    $finish_time = isset($input['finish_time']) ? trim($input['finish_time']) : '';
    if (empty($finish_time)) {
        return false;
    }

    // Validate start < finish
    if (strtotime($start_time) >= strtotime($finish_time)) {
        return false;
    }

    // Validate visit_date
    $visit_date = isset($input['visit_date']) ? trim($input['visit_date']) : '';
    if (empty($visit_date)) {
        return false;
    }

    // Validate potensi_improvement character limit
    if (isset($input['potensi_improvement']) && mb_strlen($input['potensi_improvement']) > 2000) {
        return false;
    }

    // Validate hasil_improvement character limit
    if (isset($input['hasil_improvement']) && mb_strlen($input['hasil_improvement']) > 2000) {
        return false;
    }

    // Validate kegiatan: at least 1 required
    $kegiatan_list = isset($input['kegiatan']) && is_array($input['kegiatan']) ? $input['kegiatan'] : [];
    if (empty($kegiatan_list)) {
        return false;
    }

    foreach ($kegiatan_list as $kegiatan) {
        // Validate nama_kegiatan
        $nama_kegiatan = isset($kegiatan['nama_kegiatan']) ? trim($kegiatan['nama_kegiatan']) : '';
        if (empty($nama_kegiatan)) {
            return false;
        }
        if (mb_strlen($nama_kegiatan) > 500) {
            return false;
        }

        // Validate action plans: at least 1 per kegiatan
        $action_plans = isset($kegiatan['action_plans']) && is_array($kegiatan['action_plans']) ? $kegiatan['action_plans'] : [];
        if (empty($action_plans)) {
            return false;
        }

        foreach ($action_plans as $plan) {
            // Validate description
            $description = isset($plan['description']) ? trim($plan['description']) : '';
            if (empty($description)) {
                return false;
            }
            if (mb_strlen($description) > 500) {
                return false;
            }

            // Validate PIC
            $pic = isset($plan['pic']) ? trim($plan['pic']) : '';
            if (empty($pic)) {
                return false;
            }
            if (mb_strlen($pic) > 100) {
                return false;
            }

            // Validate due_date
            $due_date = isset($plan['due_date']) ? trim($plan['due_date']) : '';
            if (empty($due_date)) {
                return false;
            }
            if (!empty($visit_date) && $due_date < $visit_date) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Check if a visit with 'final' status should allow editing.
 *
 * @param string $status The visit status
 * @return bool True if editing is allowed, false if rejected
 */
function can_edit_visit($status)
{
    return $status !== 'final';
}

/**
 * Validate character limit for improvement fields.
 *
 * @param string $text The text to validate
 * @param int $max_length Maximum allowed length (default 2000)
 * @return bool True if within limit, false if exceeds
 */
function validate_character_limit($text, $max_length = 2000)
{
    return mb_strlen($text) <= $max_length;
}

/**
 * Toggle action plan status.
 *
 * @param string $current_status Current status ('Progress' or 'Done')
 * @return string New status after toggle
 */
function toggle_action_plan_status($current_status)
{
    if ($current_status === 'Progress') {
        return 'Done';
    } elseif ($current_status === 'Done') {
        return 'Progress';
    }
    return $current_status;
}

// ============================================================================
// TEST HELPERS
// ============================================================================

$test_results = [];
$total_passed = 0;
$total_failed = 0;

/**
 * Run a property test with the given number of iterations.
 *
 * @param string $name Property name
 * @param callable $test_fn Function that returns true if property holds
 * @param int $iterations Number of random iterations
 */
function run_property_test($name, $test_fn, $iterations = 100)
{
    global $test_results, $total_passed, $total_failed;

    $failures = [];

    for ($i = 0; $i < $iterations; $i++) {
        $result = $test_fn($i);
        if ($result !== true) {
            $failures[] = [
                'iteration' => $i,
                'details' => $result
            ];
            if (count($failures) >= 3) {
                break; // Stop after 3 failures to avoid flooding output
            }
        }
    }

    if (empty($failures)) {
        echo "  PASS: {$name} ({$iterations} iterations)\n";
        $total_passed++;
        $test_results[$name] = 'PASS';
    } else {
        echo "  FAIL: {$name} (failed at iteration {$failures[0]['iteration']})\n";
        echo "        Counter-example: {$failures[0]['details']}\n";
        $total_failed++;
        $test_results[$name] = 'FAIL';
    }
}

/**
 * Generate a random integer between min and max (inclusive).
 */
function rand_int($min, $max)
{
    return mt_rand($min, $max);
}

/**
 * Generate a random float between min and max.
 */
function rand_float($min, $max, $decimals = 2)
{
    return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $decimals);
}

/**
 * Generate a random string of given length from a character set.
 */
function rand_string($length, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ')
{
    $str = '';
    $max = strlen($charset) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str .= $charset[mt_rand(0, $max)];
    }
    return $str;
}

/**
 * Generate a random date string in Y-m-d format.
 */
function rand_date($min_year = 2020, $max_year = 2030)
{
    $year = mt_rand($min_year, $max_year);
    $month = mt_rand(1, 12);
    $day = mt_rand(1, 28); // Safe for all months
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

/**
 * Generate a random time string in HH:mm format.
 */
function rand_time()
{
    $hour = mt_rand(0, 23);
    $minute = mt_rand(0, 59);
    return sprintf('%02d:%02d', $hour, $minute);
}

// ============================================================================
// PROPERTY TESTS
// ============================================================================

echo "==========================================================\n";
echo "Property-Based Tests: Laporan Kunjungan Konsultan\n";
echo "==========================================================\n\n";

// --------------------------------------------------------------------------
// Property 1: Mandays calculation consistency (Task 2.3)
// Validates: Requirements 7.3, 9.2
// --------------------------------------------------------------------------
echo "Property 1: Mandays calculation consistency\n";
echo "  Validates: Requirements 7.3, 9.2\n";

run_property_test(
    'For any valid start/finish where finish > start, mandays = ROUND((finish-start)/60/8, 2)',
    function ($iteration) {
        // Generate random start time (between 06:00 and 15:00)
        $start_hour = mt_rand(6, 15);
        $start_min = mt_rand(0, 59);

        // Ensure at least 5 minutes gap so mandays > 0 after rounding
        // (5 min / 60 / 8 = 0.01 which rounds to 0.01)
        $min_gap_minutes = 5;
        $start_total_min = $start_hour * 60 + $start_min;
        $finish_total_min = $start_total_min + mt_rand($min_gap_minutes, 480);

        $finish_hour = intdiv($finish_total_min, 60);
        $finish_min = $finish_total_min % 60;

        // Cap at 23:59
        if ($finish_hour > 23) {
            $finish_hour = 23;
            $finish_min = 59;
        }

        // Create timestamps for same day
        $base_date = '2025-01-15';
        $start_timestamp = strtotime("{$base_date} " . sprintf('%02d:%02d', $start_hour, $start_min) . ":00");
        $finish_timestamp = strtotime("{$base_date} " . sprintf('%02d:%02d', $finish_hour, $finish_min) . ":00");

        // Skip if finish <= start (edge case from random generation)
        if ($finish_timestamp <= $start_timestamp) {
            return true;
        }

        // Calculate expected mandays
        $duration_minutes = ($finish_timestamp - $start_timestamp) / 60;
        $expected_mandays = round($duration_minutes / 60 / 8, 2);

        // Calculate using our function
        $actual_mandays = calculate_mandays($start_timestamp, $finish_timestamp);

        if (abs($actual_mandays - $expected_mandays) > 0.001) {
            return "start=" . sprintf('%02d:%02d', $start_hour, $start_min)
                 . ", finish=" . sprintf('%02d:%02d', $finish_hour, $finish_min)
                 . ", expected={$expected_mandays}, actual={$actual_mandays}";
        }

        // Additional check: mandays must be non-negative (>= 0)
        // With min 5 min gap: 5/60/8 = 0.01, so should always be > 0
        if ($actual_mandays < 0) {
            return "Mandays should be non-negative for finish > start, got {$actual_mandays}";
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 2: Cumulative mandays is sum of individual visits (Task 8.3)
// Validates: Requirements 9.2, 9.3
// --------------------------------------------------------------------------
echo "Property 2: Cumulative mandays is sum of individual visits\n";
echo "  Validates: Requirements 9.2, 9.3\n";

run_property_test(
    'For any project with multiple visits, cumulative = sum of individual mandays_used',
    function ($iteration) {
        // Generate random number of visits (1 to 20)
        $num_visits = mt_rand(1, 20);
        $mandays_values = [];

        for ($i = 0; $i < $num_visits; $i++) {
            // Generate random mandays values (0.01 to 2.00)
            $mandays_values[] = rand_float(0.01, 2.00, 2);
        }

        // Calculate cumulative using our function
        $cumulative = calculate_cumulative_mandays($mandays_values);

        // Calculate expected sum manually
        $expected_sum = 0;
        foreach ($mandays_values as $val) {
            $expected_sum += $val;
        }

        // Verify cumulative equals sum (with floating point tolerance)
        if (abs($cumulative - $expected_sum) > 0.0001) {
            return "visits=" . json_encode($mandays_values)
                 . ", cumulative={$cumulative}, expected_sum={$expected_sum}";
        }

        // Verify sisa_mandays calculation
        $allocated = rand_float(5.0, 50.0, 2);
        $sisa = calculate_sisa_mandays($allocated, $cumulative);
        $expected_sisa = $allocated - $cumulative;

        if (abs($sisa - $expected_sisa) > 0.0001) {
            return "allocated={$allocated}, cumulative={$cumulative}, "
                 . "sisa={$sisa}, expected_sisa={$expected_sisa}";
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 3: Whitespace-only kegiatan rejection (Task 3.4)
// Validates: Requirements 3.7
// --------------------------------------------------------------------------
echo "Property 3: Whitespace-only kegiatan rejection\n";
echo "  Validates: Requirements 3.7\n";

run_property_test(
    'For any whitespace-only string, validate_kegiatan rejects it',
    function ($iteration) {
        // Generate random whitespace-only strings
        $whitespace_chars = [' ', "\t", "\n", "\r", "  ", "\t\t", " \t ", "\n\r", "   \t\n  "];

        // Build a random whitespace string
        $length = mt_rand(1, 20);
        $ws_string = '';
        for ($i = 0; $i < $length; $i++) {
            $ws_string .= $whitespace_chars[mt_rand(0, count($whitespace_chars) - 1)];
        }

        // Validate: should be rejected (return false)
        $result = validate_kegiatan($ws_string);

        if ($result !== false) {
            $escaped = addcslashes($ws_string, "\t\n\r");
            return "Whitespace string '{$escaped}' (len=" . strlen($ws_string) . ") was NOT rejected";
        }

        // Also verify that non-whitespace strings are accepted
        $valid_string = rand_string(mt_rand(1, 50));
        $valid_result = validate_kegiatan($valid_string);

        if ($valid_result !== true) {
            return "Valid string '{$valid_string}' was incorrectly rejected";
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 4: Action plan due date validation (Task 3.5)
// Validates: Requirements 4.7
// --------------------------------------------------------------------------
echo "Property 4: Action plan due date validation\n";
echo "  Validates: Requirements 4.7\n";

run_property_test(
    'For any due_date < visit_date, validation rejects it',
    function ($iteration) {
        // Generate a random visit date
        $visit_year = mt_rand(2023, 2026);
        $visit_month = mt_rand(1, 12);
        $visit_day = mt_rand(2, 28); // Start from 2 so we can go earlier
        $visit_date = sprintf('%04d-%02d-%02d', $visit_year, $visit_month, $visit_day);

        // Generate a due_date that is BEFORE visit_date
        $days_before = mt_rand(1, 365);
        $due_timestamp = strtotime($visit_date) - ($days_before * 86400);
        $due_date = date('Y-m-d', $due_timestamp);

        // Validate: should be rejected (return false)
        $result = validate_due_date($due_date, $visit_date);

        if ($result !== false) {
            return "due_date={$due_date} < visit_date={$visit_date} was NOT rejected";
        }

        // Also verify that due_date >= visit_date is accepted
        $days_after = mt_rand(0, 365);
        $valid_due_timestamp = strtotime($visit_date) + ($days_after * 86400);
        $valid_due_date = date('Y-m-d', $valid_due_timestamp);

        $valid_result = validate_due_date($valid_due_date, $visit_date);

        if ($valid_result !== true) {
            return "due_date={$valid_due_date} >= visit_date={$visit_date} was incorrectly rejected";
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 5: Draft save preserves all data round-trip (Task 6.3)
// Validates: Requirements 8.1, 6.5, 6.7
// --------------------------------------------------------------------------
echo "Property 5: Draft save preserves all data round-trip\n";
echo "  Validates: Requirements 8.1, 6.5, 6.7\n";

run_property_test(
    'Save data as draft (JSON encode/decode), verify all fields preserved',
    function ($iteration) {
        // Generate random visit data structure
        $num_kegiatan = mt_rand(1, 5);
        $kegiatan = [];

        for ($k = 0; $k < $num_kegiatan; $k++) {
            $num_plans = mt_rand(1, 5);
            $plans = [];

            for ($p = 0; $p < $num_plans; $p++) {
                $plans[] = [
                    'description' => rand_string(mt_rand(1, 100)),
                    'pic'         => rand_string(mt_rand(1, 50)),
                    'due_date'    => rand_date(),
                    'status'      => mt_rand(0, 1) ? 'Progress' : 'Done',
                ];
            }

            $kegiatan[] = [
                'nama_kegiatan' => rand_string(mt_rand(1, 100)),
                'id_aktifitas'  => mt_rand(0, 1) ? 'AKT-' . mt_rand(1, 999) : null,
                'is_custom'     => mt_rand(0, 1),
                'action_plans'  => $plans,
            ];
        }

        $visit_data = [
            'id_spk'              => 'SPK/' . mt_rand(1000, 9999) . '/TEST',
            'visit_date'          => rand_date(),
            'start_time'          => rand_time(),
            'finish_time'         => rand_time(),
            'duration_minutes'    => mt_rand(30, 480),
            'mandays_used'        => rand_float(0.01, 2.00, 2),
            'potensi_improvement' => mt_rand(0, 1) ? rand_string(mt_rand(0, 200)) : '',
            'hasil_improvement'   => mt_rand(0, 1) ? rand_string(mt_rand(0, 200)) : '',
            'kegiatan'            => $kegiatan,
        ];

        // Simulate round-trip (save as JSON, load back)
        $loaded_data = draft_save_roundtrip($visit_data);

        // Verify all fields are preserved
        if ($loaded_data === null) {
            return "JSON round-trip returned null for iteration {$iteration}";
        }

        // Check top-level fields
        $top_fields = ['id_spk', 'visit_date', 'start_time', 'finish_time',
                       'duration_minutes', 'mandays_used', 'potensi_improvement',
                       'hasil_improvement'];

        foreach ($top_fields as $field) {
            if (!array_key_exists($field, $loaded_data)) {
                return "Field '{$field}' missing after round-trip";
            }
            // Use loose comparison for numeric fields (JSON may convert 1.00 to 1)
            if (is_numeric($visit_data[$field]) && is_numeric($loaded_data[$field])) {
                if (abs((float)$loaded_data[$field] - (float)$visit_data[$field]) > 0.001) {
                    return "Field '{$field}' changed: original=" . json_encode($visit_data[$field])
                         . ", loaded=" . json_encode($loaded_data[$field]);
                }
            } elseif ($loaded_data[$field] !== $visit_data[$field]) {
                return "Field '{$field}' changed: original=" . json_encode($visit_data[$field])
                     . ", loaded=" . json_encode($loaded_data[$field]);
            }
        }

        // Check kegiatan array
        if (count($loaded_data['kegiatan']) !== count($visit_data['kegiatan'])) {
            return "Kegiatan count mismatch: original=" . count($visit_data['kegiatan'])
                 . ", loaded=" . count($loaded_data['kegiatan']);
        }

        // Check each kegiatan and its action plans
        for ($k = 0; $k < count($visit_data['kegiatan']); $k++) {
            $orig_keg = $visit_data['kegiatan'][$k];
            $load_keg = $loaded_data['kegiatan'][$k];

            if ($orig_keg['nama_kegiatan'] !== $load_keg['nama_kegiatan']) {
                return "Kegiatan[{$k}].nama_kegiatan mismatch";
            }

            if (count($orig_keg['action_plans']) !== count($load_keg['action_plans'])) {
                return "Kegiatan[{$k}].action_plans count mismatch";
            }

            for ($p = 0; $p < count($orig_keg['action_plans']); $p++) {
                $orig_plan = $orig_keg['action_plans'][$p];
                $load_plan = $load_keg['action_plans'][$p];

                foreach (['description', 'pic', 'due_date', 'status'] as $pf) {
                    if ($orig_plan[$pf] !== $load_plan[$pf]) {
                        return "Kegiatan[{$k}].action_plans[{$p}].{$pf} mismatch: "
                             . "'{$orig_plan[$pf]}' vs '{$load_plan[$pf]}'";
                    }
                }
            }
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 6: Final save validation completeness (Task 5.3)
// Validates: Requirements 8.3, 8.4, 8.5
// --------------------------------------------------------------------------
echo "Property 6: Final save validation completeness\n";
echo "  Validates: Requirements 8.3, 8.4, 8.5\n";

run_property_test(
    'save_final succeeds iff all required fields are valid',
    function ($iteration) {
        $visit_date = '2025-03-15';

        // Generate a fully valid input
        $valid_input = [
            'start_time'          => '08:00',
            'finish_time'         => '16:00',
            'visit_date'          => $visit_date,
            'potensi_improvement' => rand_string(mt_rand(0, 100)),
            'hasil_improvement'   => rand_string(mt_rand(0, 100)),
            'kegiatan'            => [
                [
                    'nama_kegiatan' => rand_string(mt_rand(1, 50)),
                    'action_plans'  => [
                        [
                            'description' => rand_string(mt_rand(1, 50)),
                            'pic'         => rand_string(mt_rand(1, 20)),
                            'due_date'    => '2025-03-20',
                            'status'      => 'Progress',
                        ]
                    ]
                ]
            ]
        ];

        // Valid input should pass
        if (validate_final_save($valid_input) !== true) {
            return "Valid input was rejected: " . json_encode($valid_input);
        }

        // Now test various invalid scenarios based on iteration
        $scenario = $iteration % 10;
        $invalid_input = $valid_input; // Start with valid, then break one thing

        switch ($scenario) {
            case 0: // Missing start_time
                $invalid_input['start_time'] = '';
                break;
            case 1: // Missing finish_time
                $invalid_input['finish_time'] = '';
                break;
            case 2: // start >= finish
                $invalid_input['start_time'] = '16:00';
                $invalid_input['finish_time'] = '08:00';
                break;
            case 3: // Missing visit_date
                $invalid_input['visit_date'] = '';
                break;
            case 4: // Empty kegiatan array
                $invalid_input['kegiatan'] = [];
                break;
            case 5: // Empty nama_kegiatan (whitespace only)
                $invalid_input['kegiatan'][0]['nama_kegiatan'] = '   ';
                break;
            case 6: // Empty action_plans
                $invalid_input['kegiatan'][0]['action_plans'] = [];
                break;
            case 7: // Empty description in action plan
                $invalid_input['kegiatan'][0]['action_plans'][0]['description'] = '';
                break;
            case 8: // Empty PIC
                $invalid_input['kegiatan'][0]['action_plans'][0]['pic'] = '';
                break;
            case 9: // due_date < visit_date
                $invalid_input['kegiatan'][0]['action_plans'][0]['due_date'] = '2025-03-10';
                break;
        }

        // Invalid input should fail
        if (validate_final_save($invalid_input) !== false) {
            return "Invalid input (scenario {$scenario}) was accepted: "
                 . json_encode($invalid_input);
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 7: Final status prevents editing (Task 6.4)
// Validates: Requirements 8.6
// --------------------------------------------------------------------------
echo "Property 7: Final status prevents editing\n";
echo "  Validates: Requirements 8.6\n";

run_property_test(
    'For any visit with status=final, edit operations are rejected',
    function ($iteration) {
        // Test with 'final' status — should NOT allow editing
        $result_final = can_edit_visit('final');
        if ($result_final !== false) {
            return "Visit with status='final' was allowed to edit";
        }

        // Test with 'draft' status — should allow editing
        $result_draft = can_edit_visit('draft');
        if ($result_draft !== true) {
            return "Visit with status='draft' was NOT allowed to edit";
        }

        // Generate random non-draft/non-final statuses to ensure robustness
        $random_statuses = ['final', 'Final', 'FINAL'];
        $status = $random_statuses[mt_rand(0, count($random_statuses) - 1)];

        // Only exact 'final' should be rejected
        if ($status === 'final') {
            $result = can_edit_visit($status);
            if ($result !== false) {
                return "Status '{$status}' should prevent editing but didn't";
            }
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 9: Character limit enforcement (Task 5.4)
// Validates: Requirements 6.6
// --------------------------------------------------------------------------
echo "Property 9: Character limit enforcement\n";
echo "  Validates: Requirements 6.6\n";

run_property_test(
    'For any string > 2000 chars in improvement fields, verify rejection',
    function ($iteration) {
        // Generate a string that exceeds 2000 characters
        $over_length = mt_rand(2001, 5000);
        $over_string = rand_string($over_length);

        // Should be rejected
        $result = validate_character_limit($over_string, 2000);
        if ($result !== false) {
            return "String of length {$over_length} was NOT rejected (should exceed 2000 limit)";
        }

        // Generate a string within limit
        $within_length = mt_rand(0, 2000);
        $within_string = rand_string($within_length);

        // Should be accepted
        $result_valid = validate_character_limit($within_string, 2000);
        if ($result_valid !== true) {
            return "String of length {$within_length} was rejected (should be within 2000 limit)";
        }

        // Edge case: exactly 2000 characters should be accepted
        $exact_string = rand_string(2000);
        $result_exact = validate_character_limit($exact_string, 2000);
        if ($result_exact !== true) {
            return "String of exactly 2000 chars was rejected (should be accepted)";
        }

        // Edge case: 2001 characters should be rejected
        $one_over = rand_string(2001);
        $result_one_over = validate_character_limit($one_over, 2000);
        if ($result_one_over !== false) {
            return "String of 2001 chars was accepted (should be rejected)";
        }

        return true;
    }
);

echo "\n";

// --------------------------------------------------------------------------
// Property 10: Action plan status toggle persistence (Task 8.4)
// Validates: Requirements 5.3, 5.4
// --------------------------------------------------------------------------
echo "Property 10: Action plan status toggle persistence\n";
echo "  Validates: Requirements 5.3, 5.4\n";

run_property_test(
    'Toggle status Progress<->Done produces the opposite, double toggle returns original',
    function ($iteration) {
        // Randomly pick initial status
        $initial_status = mt_rand(0, 1) ? 'Progress' : 'Done';

        // Toggle once
        $toggled = toggle_action_plan_status($initial_status);

        // Verify toggle produces opposite
        if ($initial_status === 'Progress' && $toggled !== 'Done') {
            return "Toggle from 'Progress' should give 'Done', got '{$toggled}'";
        }
        if ($initial_status === 'Done' && $toggled !== 'Progress') {
            return "Toggle from 'Done' should give 'Progress', got '{$toggled}'";
        }

        // Toggle twice should return to original
        $double_toggled = toggle_action_plan_status($toggled);
        if ($double_toggled !== $initial_status) {
            return "Double toggle should return to '{$initial_status}', got '{$double_toggled}'";
        }

        // Verify toggled status is always one of the valid values
        if (!in_array($toggled, ['Progress', 'Done'])) {
            return "Toggled status '{$toggled}' is not a valid status";
        }

        return true;
    }
);

echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================

echo "==========================================================\n";
echo "SUMMARY\n";
echo "==========================================================\n";
echo "Total Properties Tested: " . ($total_passed + $total_failed) . "\n";
echo "Passed: {$total_passed}\n";
echo "Failed: {$total_failed}\n";
echo "==========================================================\n";

if ($total_failed > 0) {
    echo "\nFailed properties:\n";
    foreach ($test_results as $name => $result) {
        if ($result === 'FAIL') {
            echo "  - {$name}\n";
        }
    }
    exit(1);
} else {
    echo "\nAll property tests PASSED!\n";
    exit(0);
}
