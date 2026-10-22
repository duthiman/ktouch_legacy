<?php include("../../inc/connection.php"); ?>
<div class="modal-content">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">NEW ATTRIBUTE</h4>
  </div>
  <div class="modal-body"> 
    <div id="results"></div>

    <div class="form-group">
      <label>Attribute Name</label>
      <input type="text" class="form-control" id="attr_name" name="attr_name" placeholder="e.g. Dry, With Ugali, Chilly" required />
    </div>

    <div class="form-group">
      <label>Affects Price?</label>
      <select class="form-control" id="affects_price" name="affects_price">
        <option value="0">No</option>
        <option value="1">Yes</option>
      </select>
    </div>

    <div class="form-group" id="price_change_group" style="display:none;">
      <label>Price Change (KES)</label>
      <input type="number" class="form-control" id="price_change" name="price_change" step="0.01" value="0" />
    </div>

    <div class="form-group">
      <label>Status</label>
      <select class="form-control" id="status" name="status">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
    </div>

  </div>

  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
    <button id="save_attr" class="btn btn-sm btn-success">Save</button>
  </div>
</div>

<script type="text/javascript">
  $('#affects_price').change(function() {
    $('#price_change_group').toggle($(this).val() == '1');
  });

  $('#save_attr').on('click', function() {
    var name = $('#attr_name').val().trim();
    var affects_price = $('#affects_price').val();
    var price_change = affects_price == '1' ? $('#price_change').val() : 0;
    var status = $('#status').val();

    if (name === "") {
      $('#results').html('<div class="alert alert-warning">Attribute name is required.</div>');
      return;
    }

    $.ajax({
      url: attrApiUrl,
      type: 'POST',
      data: {
        action: 'create_attribute',
        name: name,
        affects_price: affects_price,
        price_change: price_change,
        status: status
      },
      dataType: 'json',
      success: function(res) {
        if (res && res.ok) {
            $('#results').html('<div class="alert alert-success">' + (res.message || 'Attribute saved successfully.') + '</div>');
            setTimeout(function() {
                $('#modal').modal('hide'); // assumes your modal has ID "modal"
                window.location.reload();
            }, 1000);
            return;
        }
        $('#results').html('<div class="alert alert-danger">' + ((res && res.message) ? res.message : 'Error saving attribute.') + '</div>');
      },
      error: function() {
        $('#results').html('<div class="alert alert-danger">Error saving attribute.</div>');
      }
    });
  });
</script>
