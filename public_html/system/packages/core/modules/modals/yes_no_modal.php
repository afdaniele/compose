<div class="modal fade modal-vertical-centered" id="yes-no-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 style="margin:0 4px 0 4px">
                    <i class="bi bi-question-square"></i> Confirm?
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="width:100%" id="question-p"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    No
                </button>
                <button type="button" class="btn btn-success" id="yes-button">
                    Yes
                </button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    let configured = false;
    let yesNoSize = 'sm';
    let yesfunction = null;
    let showPleaseWaitModal = true;

    $('#yes-no-modal').on('show.bs.modal', function (e) {
        if (!configured) {
            // set size
            $('#yes-no-modal .modal-dialog').addClass('modal-' + yesNoSize);
            // set question
            var question = $(e.relatedTarget).data('question');
            $('#yes-no-modal #question-p').html(question);
            //
            var url = $(e.relatedTarget).data('url');
            $('#yes-no-modal #yes-button').data('url', url);
            //
            configured = true;
        }
    });


    $('#yes-no-modal #yes-button').on('click', function () {
        let modal = new bootstrap.Modal(document.getElementById('yes-no-modal'));
        modal.hide();
        if (showPleaseWaitModal) {
            showPleaseWait()
        }
        if (yesfunction != null) {
            yesfunction();
            if (showPleaseWaitModal) {
                hidePleaseWait()
            }
        } else {
            var url = $('#yes-no-modal #yes-button').data('url');
            //
            $.ajax({
                type: 'GET', url: url, dataType: 'json', success: function (result) {
                    if (result.code === 200) {
                        if (showPleaseWaitModal) {
                            hidePleaseWait()
                        }
                        showSuccessModal(2000, function () {
                            window.location.reload();
                        });
                    } else {
                        // error, open an alert
                        openAlert('danger', result.message);
                        if (showPleaseWaitModal) {
                            hidePleaseWait()
                        }
                    }
                }, error: function () {
                    // error
                    openAlert('danger', 'An error occurred while communicating with the server. Please, try again!');
                    if (showPleaseWaitModal) {
                        hidePleaseWait()
                    }
                }
            });
        }
    });

    function openYesNoModal(question, yes_fcn, silentMode, size) {
        $('#yes-no-modal #question-p').html(question);
        size = (size == undefined) ? 'sm' : size;
        yesfunction = yes_fcn;
        showPleaseWaitModal = !silentMode;
        yesNoSize = size;
        //
        configured = true;
        //
        let modal = new bootstrap.Modal(document.getElementById('yes-no-modal'));
        modal.show();
    }//openYesNoModal

</script>
