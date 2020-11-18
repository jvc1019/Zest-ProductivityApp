<!-- 
    ORIGINAL CODE AND MARKUP by Janley Molina
    Derivatives of this code/markup are covered by https://opensource.org/licenses/CDDL-1.0
-->

<?php
include("header.php");
include("user_details.php");
include("notification.php");
?>
<!-- Upon page load, the task list, grouped if "completed" or "not completed", will show. 
If the user presses the check button, the task_isDone of the tasks item is marked as true. The page will then be refreshed. 
If the user presses the "add new task" button, a pop-up will appear, asking for the details. -->

<body>
    <!-- navigation bar -->
    <?php include("navbar.php"); ?>
    <div class="container">
        <div class="alert alert-light shadow sticky-top">
            <!-- Tasks | sort by | sort direction | search box | add new task -->
            <!--   2   |          3               |      5     |       2      -->
            <div class="row form-inline">
                <div class="col-sm-2">
                    <h3 class="text-primary text-center">Tasks</h3>
                </div>
                <!-- Sort by and sort direction -->
                <div class="col-sm-3 form-inline">
                    <select id="sortBy" class="btn btn-sm">
                        <?php
                        $value = !empty($_GET['sortBy']) ? $_GET['sortBy'] : 0;
                        if ($value == 0) {
                        ?>
                            <option selected value="0">Name</option>
                            <option value="1">Due date</option>
                        <?php
                        } elseif ($value == 1) {
                        ?>
                            <option value="0">Name</option>
                            <option selected value="1">Due date</option>
                        <?php
                        }
                        ?>
                    </select>
                    <select id="sortDir" class="btn btn-sm">
                        <?php
                        $value = !empty($_GET['sortDir']) ? $_GET['sortDir'] : 0;
                        if ($value == 0) {
                        ?>
                            <option selected value="0">Ascending</option>
                            <option value="1">Descending</option>
                        <?php
                        } elseif ($value == 1) {
                        ?>
                            <option value="0">Ascending</option>
                            <option selected value="1">Descending</option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <!-- Search box -->
                <div class="col-sm-5 input-group">
                    <input type="text" class="form-control text-truncate border-primary border-top-0 border-left-0 border-right-0 rounded-0" id="search" placeholder="Search tasks by name..." value="<?php if (!empty($_GET['search']) && empty($_GET['tag'])) {
                                                                                                                                                                                                            echo $_GET['search'];
                                                                                                                                                                                                        } else {
                                                                                                                                                                                                            echo "";
                                                                                                                                                                                                        }
                                                                                                                                                                                                        ?>">
                    <div class="input-group-append">
                        <button id="search_clear" class="btn border-primary border-top-0 border-left-0 border-right-0 rounded-0" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                </div>
                <!-- New task button -->
                <div class="col-sm-2">
                    <button href="#addtask" data-toggle="modal" class="btn btn-sm btn-outline-primary">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                        </svg>
                        New task
                    </button>
                </div>

                <?php
                $sortBy = "task_Name";
                if (!empty($_GET['sortBy'])) {
                    $sortBySet = $_GET['sortBy'];
                    $sortBy = ($sortBySet == 0) ? "task_Name" : "task_Due";
                }

                $sortDir = "ASC";
                if (!empty($_GET['sortDir'])) {
                    $sortDirSet = $_GET['sortDir'];
                    $sortDir = ($sortDirSet == 0) ? "ASC" : "DESC";
                }

                $search = "";
                $searchQuery = "";
                if (!empty($_GET['search'])) {
                    $searchQuery = $_GET['search'];
                    $search = "WHERE task_Name LIKE '%$searchQuery%' AND task.user_ID=$user_ID ORDER BY $sortBy $sortDir";
                }


                if (!empty($_GET['tag'])) {
                    $tag = $_GET['tag'];
                    $regex = preg_quote("\b$tag\b");
                    $search = "WHERE task_Tags RLIKE '$regex' AND task.user_ID=$user_ID ORDER BY task_Name ASC";
                }
                ?>
            </div>

        </div>
        <?php include('tasks_modal_add.php'); ?>
        <script>
            var alarms = {}; // stores the reminder timestamps of the tasks
        </script>

        <?php
        if (empty($search)) {
            include('tasks_list.php');
        } else {
            include('tasks_filter.php');
        } ?>
    </div>
    <script src="js/tasks_modal_functions.js"></script>
    <script>
        // Enable all tooltips
        $(function() {
            $("[data-toggle='tooltip']").tooltip()
        })

        $(document).ready(function() {
            // Switch focus to search box by default
            $("#search").focus();
            var tmpStr = $("#search").val();
            $("#search").val("");
            $("#search").val(tmpStr);

            // ALARM FEATURE
            // collect all the alarm times, call setAlarm for each
            for (const task_Name in alarms) {
                if (alarms.hasOwnProperty(task_Name)) {
                    setAlarm(alarms[task_Name], task_Name);
                }
            }

            function setAlarm(datetime, task_Name) {
                var alarmTime = new Date(parseInt(datetime.substr(0, 4)), parseInt(datetime.substr(5, 2)) - 1, parseInt(datetime.substr(8, 2)), parseInt(datetime.substr(11, 2)), parseInt(datetime.substr(14, 2)), parseInt(datetime.substr(17, 2)));
                var duration = alarmTime.getTime() - (new Date()).getTime();
                if (isNaN(duration) || duration < 0) {
                    return;
                }

                var timer = setTimeout(function(e) {
                    window.location.search = "status_heading=Reminder" + "&status=" + task_Name + "&isAlarm=true";
                }, duration);
            }
            // END OF ALARM FEATURE

            // clears the search box 
            $("#search_clear").on('click', function(e) {
                $("#search").val("");
                window.location = "tasks.php";
            });

            // SORTING HANDLER
            // Sorts the tasks list
            $("#sortBy").on('change', sort);
            $("#sortDir").on('change', sort);
            $("#search").on('input', sort);

            function sort() {
                $sortBy = $("#sortBy").val();
                $sortDir = $("#sortDir").val();
                $searchQuery = $("#search").val();
                window.location.search = "sortBy=" + $sortBy + "&sortDir=" + $sortDir + "&search=" + $searchQuery;
            }
            // END OF SORTING HANDLER

            // Show completed tasks button
            $("#show_completed_tasks").click(function(e) {
                if ($("#completed_tasks").is(":hidden")) {
                    $(this).text("\u2191 Hide completed tasks");
                } else {
                    $(this).text("\u2193 Show completed tasks");
                }
            });

            // Marks task as complete 
            $(".checkbox").click(function(e) {
                var $task_ID = $(this).val();
                var $isChecked = ($(this).attr("checked") === undefined) ? "false" : "true";

                window.location = "tasks_update.php?task_ID=" + $task_ID + "&task_isChecked=" + $isChecked;
            });

            // reload the browser every midnight to update the Due today section
            const c_Time = new Date();
            const midnight = new Date((new Date(c_Time.getFullYear(),
                c_Time.getMonth(),
                c_Time.getDate(),
                0, 0, 0, 0).getTime()) + 86400000);

            var duration = midnight.getTime() - c_Time.getTime();

            setTimeout(setInterval(function() {
                window.location.reload;
            }, 86400000), duration);
        });
    </script>
</body>

</html>