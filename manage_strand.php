<?php include 'db_connect.php'; ?>

<?php
if (isset($_GET['id'])) {
    $strand = $conn->query("SELECT * FROM strands WHERE id = " . $_GET['id'])->fetch_assoc();
}
?>

<head>
    <link rel="stylesheet" href="assets/css/form-styles.css">
</head>

<div class="container-fluid">
    <form id="manage-strand">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($strand['id']) ? $strand['id'] : ''; ?>">
        <div class="form-group">
            <label for="code">Strand Code</label>
            <input type="text" name="code" id="code" class="form-control" value="<?php echo isset($strand['code']) ? $strand['code'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="name">Strand Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo isset($strand['name']) ? $strand['name'] : ''; ?>" required>
        </div>
        <div class="form-group text-right">
            <button class="btn btn-primary mr-2" id="submit-btn" type="submit">Save</button>
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Please select here",
            width: "100%"
        });
        // Clear any previous error message
        $('#msg').html('');
        
        // Add event listener for input changes to clear error messages
        $('input, select').on('change input', function() {
            $('#msg').html('');
        });
    });
    
    $('#manage-strand').submit(function(e) {
        e.preventDefault();
        
        // Disable submit button to prevent double submission
        $('#submit-btn').prop('disabled', true);
        
        start_load();
        
        // Get form values
        var code = $('#code').val().trim();
        var name = $('#name').val().trim();
        var id = $('input[name="id"]').val();
        
        // Client-side validation
        if(code === '' || name === '') {
            $('#msg').html('<div class="alert alert-danger">Please fill all required fields</div>');
            $('#submit-btn').prop('disabled', false);
            end_load();
            return false;
        }
        
        // Log the data being sent (for debugging)
        console.log("Sending form data:", {
            id: id,
            code: code,
            name: name
        });
        
        $.ajax({
            url: 'ajax.php?action=save_strand',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                // Log the raw response for debugging
                console.log("Raw response:", resp);
                
                try {
                    var result = JSON.parse(resp);
                    console.log("Parsed response:", result);
                    
                    if (result.status == 1) {
                        alert_toast("Strand successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#msg').html('<div class="alert alert-danger">' + (result.message || 'An error occurred') + '</div>');
                        $('#submit-btn').prop('disabled', false);
                    }
                } catch(e) {
                    // Fallback for non-JSON responses
                    console.error("JSON parse error:", e);
                    console.error("Invalid JSON response:", resp);
                    
                    if(resp.trim() == '1') {
                        alert_toast("Strand successfully saved", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#msg').html('<div class="alert alert-danger">Server returned invalid response</div>');
                        console.error("Error parsing response: ", e, resp);
                        $('#submit-btn').prop('disabled', false);
                    }
                }
                end_load();
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response:", xhr.responseText);
                $('#msg').html('<div class="alert alert-danger">An error occurred: ' + error + '</div>');
                $('#submit-btn').prop('disabled', false);
                end_load();
            }
        });
    });
</script>