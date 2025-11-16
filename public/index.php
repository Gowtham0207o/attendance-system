<?php
   require_once __DIR__ . '/../app/bootstrap.php';
   session_start();
   require_once __DIR__ . '/../app/Lib/auth.php';
   //checkAuth(); // Only logged-in users can access
   
   // Now you can include header, sidebar, and content
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Attendance System</title>
      <!-- Bootstrap CSS -->
      <!-- CSS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- DataTables CSS -->
      <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">
      <!-- Custom CSS -->
      <link rel="stylesheet" href="assets/css/style.css">
      <link rel="stylesheet" href="assets/css/custom.css">
   </head>
   <body class="bg-dark text-light">
      <!-- =======================
         Include reusable views
         ======================= -->
      <?php include 'views/header.php'; ?>
      <div class="container-fluid mt-4">
         <div class="row g-4">
            <div class="col-lg-7">
               <div class="card glass p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                     <h5 class="mb-0">Master Calendar</h5>
                     <div class="text-muted small">Live sync · colour: labour / employees</div>
                  </div>
                  <div id="calendar"></div>
               </div>
               <div class="card glass p-3 mt-3">
                   <div class="d-flex justify-content-between align-items-center">
        <h6 class="m-0">Attendance Insights</h6>

        <!-- Small thin button -->
        <button class="btn btn-sm btn-outline-light py-0 px-2" id="exportLabourDayAttendance">
            Export
        </button>
    </div>
                  <div class="row text-center mt-3">
                     <div class="col-4">
                        <div class="fs-3" id="ins-present"></div>
                        <small class="text">Present</small>
                     </div>
                     <div class="col-4">
                        <div class="fs-3" id="ins-absent"></div>
                        <small class="text">Absent</small>
                     </div>
                     <div class="col-4">
                        <div class="fs-3" id="ins-leave"></div>
                        <small class="text">Leave</small>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-lg-5">
               <!-- Tabs -->
               <div class="card glass p-3">
                  <ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
                     <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="labour-tab" data-bs-toggle="tab" data-bs-target="#labour" type="button">Labour</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button">Employees</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button">Payroll</button>
                     </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#additems" type="button">
                        Items
                        </button>
                     </li>
                  </ul>
                  <div class="tab-content">
                     <!-- Labour Tab -->
                     <div class="tab-pane fade show active" id="labour" data-project-id="1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                           <?php
                              // Fetch projects (formerly sites)
                              $stmt = db()->prepare("SELECT id, name FROM projects ORDER BY name ASC");
                              $stmt->execute();
                              $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                              ?>
                           <!-- Replace hardcoded site name with dropdown -->
                           <div class="d-inline justify-content-between align-items-center mb-2">
                              <div>
                                 <strong>Site: </strong>
                                 <select id="projectSelect" class="form-select form-select-sm">
                                    <?php foreach($projects as $project): ?>
                                    <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
                                    <?php endforeach; ?>
                                 </select>
                              </div>
                           </div>
                           </strong>
                           <div>
                              <button class="btn btn-sm btn-outline-light" 
                                 id="createProject"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addProjectModal">
                              + Add new project
                              </button>
                           </div>
                        </div>
                        <div class="timeline" id="labourList"></div>
                        <div class="mt-3 d-flex justify-content-between">
                           <input id="labourFilter" class="form-control form-control-sm" placeholder="Search labour name or role">
                           <button class="btn btn-sm btn-outline-light ms-2" id="bulkMarkAbsent">Mark All Present</button>
                        </div>
                     </div>
                     <!-- Employee Tab -->
                     <div class="tab-pane fade" id="employee">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                           <strong>Office Staff</strong>
                           <button class="btn btn-sm btn-outline-light" 
                              id="openAddEmp"
                              data-bs-toggle="modal"
                              data-bs-target="#addEmpModal">
                           + Add Employee
                           </button>
                        </div>
                        <div class="attendance-row" id="employeeList"></div>
                     </div>
                     <!-- Payroll Tab -->
                     <div class="tab-pane fade" id="payroll">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                           <strong>Payroll Runs</strong>
                           <button class="btn btn-sm btn-outline-light" id="createPayrun">New Pay Run</button>
                        </div>
                        <table id="payrollTable" class="display w-100">
                           <thead>
                              <tr>
                                 <th>Run</th>
                                 <th>Date</th>
                                 <th>Total Net</th>
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody></tbody>
                        </table>
                     </div>
                     <!-- ⭐ ITEMS TAB -->
                     <div class="tab-pane fade" id="additems">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                           <strong>Project Items</strong>
                           <div class="btn-group" role="group">
                              <!-- Add Item -->
                              <button class="btn btn-sm btn-outline-light"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addItemModal">
                              + Add Item
                              </button>
                              <!-- Add Labour -->
                              <button class="btn btn-sm btn-outline-light"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addLabourModal">
                              + Add Labour
                              </button>
                              <!-- Add Employee -->
                              <button class="btn btn-sm btn-outline-light"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addEmpModal">
                              + Add Employee
                              </button>
                              <!-- Add Project -->
                              <button class="btn btn-sm btn-outline-light"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addProjectModal">
                              + Add Project
                              </button>
                           </div>
                        </div>
                        <div id="itemList" class="mt-2"></div>
                     </div>
                  </div>
               </div>
               <div class="card glass p-3 mt-3">
                  <div class="d-flex justify-content-between align-items-center">
                     <div>
                        <div class="small text-muted">Timezone</div>
                        <div class="fw-bold">Asia/Kolkata</div>
                     </div>
                     <button class="btn btn-sm" id="exportCsv">Employee Attendance download</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <footer class="text-muted py-5">
         <div class="container">
            <p class="float-end mb-1">
               <a href="#">Back to top</a>
            </p>
            <p class="mb-1" style="color:biege;">site developed by <a href="https://gowtham.selfmade.one/">Gowtham</a></p>
            <p class="mb-0" style="color:biege;">contact<a href="https://www.instagram.com/gowtham.ravi_">instagram</a> or connect on <a href="https://www.linkedin.com/in/gowtham-ravi02/">linkedin</a>.</p>
         </div>
      </footer>
      <!-- Modals -->
      <?php include 'views/modals/add_employee_modal.php'; ?>
      <?php include 'views/modals/add_labour_modal.php'; ?>
      <?php include 'views/modals/add_project_modal.php'; ?>
      <!-- =======================
         Scripts
         ======================= -->
      <!-- jQuery -->
      <!-- jQuery FIRST -->
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script>
         $(document).ready(function() {
             const calendarEl = document.getElementById('calendar');
             const calendar = new FullCalendar.Calendar(calendarEl, {
                 initialView: 'dayGridMonth',
                 height: 560,
                 headerToolbar: {
                     left: 'prev,next today',
                     center: 'title',
                     right: 'dayGridMonth,timeGridWeek'
                 },
                 events: 'api/attendance/fetch_calendar_events.php',
                 dateClick: function(info) {
                     loadAttendanceSummary(info.dateStr);
                 }
             });
         
             calendar.render();
         
             // Load daily summary into the "Attendance Insights" card
             function loadAttendanceSummary(date) {
                 $("#ins-date").text("Loading...");
                         $("#ins-present").text("—");
                                 $("#ins-absent").text("—");
                                         $("#ins-leave").text("—");
         
                                                 $.ajax({
                                                             url: "api/attendance/fetch_summary.php",
                                                                         method: "GET",
                                                                                     data: { date: date },
                                                                                                 dataType: "json",
                                                                                                             success: function(res) {
                                                                                                                             if (res.success) {
                                                                                                                                                 $("#ins-present").text(res.present);
                                                                                                                                                                     $("#ins-absent").text(res.absent);
                                                                                                                                                                                         $("#ins-leave").text(res.leave);
                                                                                                                                                                                                             $("#ins-date").html(`<span class="text-info">${res.date}</span>`);
                                                                                                                                                                                                                             } else {
                                                                                                                                                                                                                                                 $("#ins-present, #ins-absent, #ins-leave").text("0");
                                                                                                                                                                                                                                                                     $("#ins-date").html(`<span class="text-warning">${res.message || "No data"}</span>`);
                                                                                                                                                                                                                                                                                     }
                                                                                                                                                                                                                                                                                                 },
                                                                                                                                                                                                                                                                                                             error: function() {
                                                                                                                                                                                                                                                                                                                             $("#ins-date").html('<span class="text-danger">Error fetching data</span>');
                                                                                                                                                                                                                                                                                                                                         }
                                                                                                                                                                                                                                                                                                                                                 });
             }
         });
      </script>
      <!-- Bootstrap JS (depends on jQuery if version < 5) -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <!-- DataTables / Plugins -->
      <script src="assets/js/lib/datatables.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      <!-- Your app scripts LAST -->
      <script src="assets/js/app/common.js"></script>
      <script src="assets/js/app/labours.js"></script>
      <script src="assets/js/app/payroll.js"></script>
      <script src="assets/js/app/projects.js"></script>
      <script src="assets/js/app/employees.js"></script>
      <script src="assets/js/main.js"></script>
   </body>
</html>