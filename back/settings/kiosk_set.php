<!-- begin panel -->
<div class="panel">
    <div class="panel-body">
        <h4><i class="fa fa-desktop"></i> Front Panel / Kiosk Launcher</h4>
        <p class="text-muted" style="font-size:13px;">
            Configure and download a Windows Task Scheduler file that automatically opens the front panel in the browser on startup / logon.
        </p>

        <div class="row">
            <div class="col-sm-6">
                <fieldset>
                    <legend class="m-b-15">LAUNCHER SETTINGS</legend>

                    <div class="form-group row m-b-10">
                        <label class="col-md-5" style="color:blue; padding-top:8px;">Browser</label>
                        <div class="col-md-7">
                            <select class="form-control" id="kiosk_browser" name="kiosk_browser">
                                <option value="chrome">Google Chrome</option>
                                <option value="edge">Microsoft Edge</option>
                                <option value="firefox">Mozilla Firefox</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row m-b-10">
                        <label class="col-md-5" style="color:blue; padding-top:8px;">Zoom Level</label>
                        <div class="col-md-7">
                            <select class="form-control" id="kiosk_zoom" name="kiosk_zoom">
                                <option value="0.50">50%</option>
                                <option value="0.60">60%</option>
                                <option value="0.67" selected>67% (Default)</option>
                                <option value="0.75">75%</option>
                                <option value="0.80">80%</option>
                                <option value="0.90">90%</option>
                                <option value="1.00">100%</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row m-b-10">
                        <label class="col-md-5" style="color:blue; padding-top:6px;">Kiosk Mode</label>
                        <div class="col-md-7">
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="kiosk_mode" id="kiosk_yes" value="1" checked />
                                <label for="kiosk_yes" style="color:blue;">YES</label>
                            </div>
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="kiosk_mode" id="kiosk_no" value="0" />
                                <label for="kiosk_no" style="color:blue;">NO</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row m-b-10">
                        <label class="col-md-5" style="color:blue; padding-top:8px;">Front Panel URL</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="kiosk_url"
                                   value="http://localhost/clients/ktouch/gpints/front/kwedia/"
                                   placeholder="http://localhost/..." />
                        </div>
                    </div>

                    <div class="form-group row m-b-10">
                        <label class="col-md-5" style="color:blue; padding-top:8px;">Task Name</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="kiosk_taskname"
                                   value="KTouch Front Panel"
                                   placeholder="Task Scheduler task name" />
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-sm-6">
                <fieldset>
                    <legend class="m-b-15">HOW TO USE</legend>
                    <ol style="font-size:13px; line-height:1.9;">
                        <li>Choose your browser, zoom level, and kiosk mode.</li>
                        <li>Click <strong>Download Task File</strong>.</li>
                        <li>Open <strong>Windows Task Scheduler</strong> (search in Start menu).</li>
                        <li>Click <strong>Action &rarr; Import Task&hellip;</strong></li>
                        <li>Select the downloaded <code>.xml</code> file and click <strong>OK</strong>.</li>
                        <li>The front panel will now open automatically at logon.</li>
                    </ol>
                    <div class="alert alert-warning" style="font-size:12px; margin-top:10px;">
                        <i class="fa fa-info-circle"></i>
                        <strong>Firefox note:</strong> Firefox does not support command-line zoom. Use Chrome or Edge for zoom control.
                    </div>
                    <div class="alert alert-info" style="font-size:12px;">
                        <i class="fa fa-info-circle"></i>
                        Zoom level uses <code>--force-device-scale-factor</code> (Chrome/Edge only). Kiosk mode hides browser chrome &amp; runs fullscreen.
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="panel-footer text-right">
        <button type="button" class="btn btn-primary" onclick="downloadKioskTask();">
            <i class="fa fa-download"></i> Download Task File
        </button>
    </div>
</div>

<script type="text/javascript">
function downloadKioskTask() {
    var browser   = document.getElementById('kiosk_browser').value;
    var zoom      = document.getElementById('kiosk_zoom').value;
    var kiosk     = document.querySelector('input[name="kiosk_mode"]:checked').value;
    var url       = document.getElementById('kiosk_url').value.trim();
    var taskname  = document.getElementById('kiosk_taskname').value.trim();

    if (!url) {
        var hulla = new hullabaloo();
        hulla.send("Please enter the Front Panel URL.", "danger");
        return;
    }

    // Build a hidden form and POST to the download script
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../settings/kiosk_download.php';
    form.target = '_blank';

    var fields = { browser: browser, zoom: zoom, kiosk: kiosk, url: url, taskname: taskname };
    for (var key in fields) {
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = key;
        input.value = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
