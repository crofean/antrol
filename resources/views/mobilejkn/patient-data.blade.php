@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Patient Data for Task ID</div>

                <div class="card-body">
                    <form id="patientDataForm" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="regNo" name="regNo" placeholder="Enter Registration Number">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Get Data</button>
                            </div>
                        </div>
                    </form>

                    <div id="patientDataResults" class="d-none">
                        <div class="alert alert-info mb-4">
                            <h5>Patient Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="patientName"></span></p>
                                    <p><strong>MRN:</strong> <span id="patientMRN"></span></p>
                                    <p><strong>Registration No:</strong> <span id="registrationNo"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Doctor:</strong> <span id="doctorName"></span></p>
                                    <p><strong>Poly:</strong> <span id="polyName"></span></p>
                                    <p><strong>Visit Date:</strong> <span id="visitDate"></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">BPJS Reference</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Booking Code:</strong> <span id="bookingCode"></span></p>
                                <p><strong>BPJS Number:</strong> <span id="bpjsNumber"></span></p>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Task Timestamps</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Task ID</th>
                                            <th>Description</th>
                                            <th>Timestamp</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="taskTable">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button id="updateAllTasks" class="btn btn-success">Update All Missing Tasks</button>
                            </div>
                        </div>
                    </div>

                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const taskDescriptions = {
        '1': 'Booking',
        '2': 'Check-in',
        '3': 'In Service',
        '4': 'Doctor Service',
        '5': 'Pharmacy Service',
        '6': 'Pharmacy Payment',
        '7': 'Finished',
        '99': 'Canceled'
    };
    
    $(document).ready(function() {
        $('#patientDataForm').submit(function(e) {
            e.preventDefault();
            
            const regNo = $('#regNo').val().trim();
            if (!regNo) {
                showError('Please enter a valid registration number');
                return;
            }
            
            fetchPatientData(regNo);
        });
        
        $('#updateAllTasks').click(function() {
            const bookingCode = $('#bookingCode').text();
            if (!bookingCode) return;
            
            updateMissingTasks(bookingCode);
        });
    });
    
    function fetchPatientData(regNo) {
        $.ajax({
            url: `/api/mobilejkn/get-patient-data/${regNo}`,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#patientDataResults').addClass('d-none');
                $('#errorMessage').addClass('d-none');
            },
            success: function(response) {
                if (response.status) {
                    displayPatientData(response.data);
                } else {
                    showError(response.message || 'Failed to retrieve patient data');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while fetching data';
                showError(message);
            }
        });
    }
    
    function displayPatientData(data) {
        // Basic patient info
        $('#patientName').text(data.registration.nm_pasien || '-');
        $('#patientMRN').text(data.registration.no_rkm_medis || '-');
        $('#registrationNo').text(data.registration.no_rawat || '-');
        
        // Doctor and clinic info
        $('#doctorName').text(data.doctor ? data.doctor.nm_dokter : '-');
        $('#polyName').text(data.registration.nm_poli || '-');
        $('#visitDate').text(formatDate(data.registration.tgl_registrasi) || '-');
        
        // BPJS info
        $('#bookingCode').text(data.kodebooking || '-');
        $('#bpjsNumber').text(data.referral ? data.referral.no_kartu : '-');
        
        // Task timestamps
        populateTaskTable(data.task_timestamps, data.kodebooking);
        
        $('#patientDataResults').removeClass('d-none');
    }
    
    function populateTaskTable(timestamps, kodebooking) {
        const taskTable = $('#taskTable');
        taskTable.empty();
        
        // Task IDs 3-7 are relevant for most outpatient visits
        [3, 4, 5, 6, 7].forEach(taskId => {
            const timestamp = timestamps[taskId];
            
            const row = $('<tr>');
            row.append($('<td>').text(taskId));
            row.append($('<td>').text(taskDescriptions[taskId] || '-'));
            
            // Timestamp cell
            const tsCell = $('<td>');
            if (timestamp) {
                tsCell.text(formatDateTime(new Date(parseInt(timestamp))));
            } else {
                tsCell.text('Not recorded');
            }
            row.append(tsCell);
            
            // Action button
            const actionCell = $('<td>');
            const actionBtn = $('<button>')
                .addClass('btn btn-sm ' + (timestamp ? 'btn-warning' : 'btn-primary'))
                .text(timestamp ? 'Update' : 'Send')
                .data('taskid', taskId)
                .data('kodebooking', kodebooking)
                .click(function() {
                    updateTaskId($(this).data('kodebooking'), $(this).data('taskid'));
                });
            actionCell.append(actionBtn);
            row.append(actionCell);
            
            taskTable.append(row);
        });
    }
    
    function updateTaskId(kodebooking, taskId) {
        $.ajax({
            url: '/api/mobilejkn/update-task-id-now',
            type: 'POST',
            dataType: 'json',
            data: {
                kodebooking: kodebooking,
                taskid: taskId
            },
            success: function(response) {
                if (response.metadata && response.metadata.code === 200) {
                    alert('Task ID ' + taskId + ' updated successfully');
                    // Refresh data
                    fetchPatientData($('#regNo').val().trim());
                } else {
                    alert('Failed to update task: ' + (response.metadata?.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error communicating with server');
            }
        });
    }
    
    function updateMissingTasks(kodebooking) {
        // Find task IDs that don't have timestamps
        const missingTasks = [];
        $('#taskTable tr').each(function() {
            const row = $(this);
            const taskId = row.find('td').first().text();
            const hasTimestamp = row.find('td:nth-child(3)').text() !== 'Not recorded';
            
            if (!hasTimestamp) {
                missingTasks.push(taskId);
            }
        });
        
        if (missingTasks.length === 0) {
            alert('No missing tasks to update');
            return;
        }
        
        // Confirm with user
        if (!confirm('Update these task IDs: ' + missingTasks.join(', ') + '?')) {
            return;
        }
        
        // Use batch update endpoint
        $.ajax({
            url: '/api/mobilejkn/batch-update-task-ids',
            type: 'POST',
            dataType: 'json',
            data: {
                kodebooking: kodebooking,
                taskids: missingTasks
            },
            success: function(response) {
                if (response.status) {
                    alert('Successfully updated ' + response.data.updated + ' task(s)');
                    // Refresh data
                    fetchPatientData($('#regNo').val().trim());
                } else {
                    alert('Failed to update tasks: ' + response.message);
                }
            },
            error: function() {
                alert('Error communicating with server');
            }
        });
    }
    
    function showError(message) {
        $('#errorMessage').text(message).removeClass('d-none');
        $('#patientDataResults').addClass('d-none');
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        
        try {
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        } catch (e) {
            return dateStr;
        }
    }
    
    function formatDateTime(date) {
        if (!(date instanceof Date) || isNaN(date)) return '-';
        
        return date.toLocaleDateString() + ' ' + 
               date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
</script>
@endsection
