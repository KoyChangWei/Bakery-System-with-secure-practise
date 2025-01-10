<div class="mb-6">
    <button onclick="showScheduleModal()"
        class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add New Schedule
    </button>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="px-6 py-3 text-left">Product</th>
                <th class="px-6 py-3 text-left">Production Date</th>
                <th class="px-6 py-3 text-left">Order Volume</th>
                <th class="px-6 py-3 text-left">Production Capacity</th>
                <th class="px-6 py-3 text-left">Staff Assigned</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php
            $schedule_sql = "SELECT ps.*, r.recipe_name, GROUP_CONCAT(a.name_tbl SEPARATOR ', ') as staff_names 
                           FROM production_schedule ps
                           JOIN recipe_db r ON ps.product_id = r.recipe_id
                           LEFT JOIN production_staff_assignment psa ON ps.schedule_id = psa.schedule_id
                           LEFT JOIN admin_db a ON psa.staff_id = a.admin_id
                           GROUP BY ps.schedule_id
                           ORDER BY ps.production_date";
            $schedule_result = $conn->query($schedule_sql);

            if ($schedule_result && $schedule_result->num_rows > 0) {
                while ($row = $schedule_result->fetch_assoc()) {
                    echo "<tr class='hover:bg-gray-50'>";
                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['recipe_name']) . "</td>";
                    echo "<td class='px-6 py-4'>" . date('Y-m-d', strtotime($row['production_date'])) . "</td>";
                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['order_volume']) . "</td>";
                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['production_capacity']) . "</td>";
                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['staff_names']) . "</td>";
                    echo "<td class='px-6 py-4'>
                            <button onclick='editSchedule(" . json_encode($row) . ")' 
                                    class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors mr-2'>
                                <i class='fas fa-edit'></i> Edit
                            </button>
                            <button onclick='deleteSchedule(" . $row['schedule_id'] . ")'
                                    class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors'>
                                <i class='fas fa-trash'></i> Delete
                            </button>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='px-6 py-4 text-center text-gray-500'>No schedules found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>