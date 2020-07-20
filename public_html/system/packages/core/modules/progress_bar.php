<style type="text/css">
    #compose_progress_bar.progress {
        height: 8px;
        display: none;
        width: 100%;
        margin: 0;
    }
</style>

<div id="compose_progress_bar" class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"
         aria-valuemax="100">
    </div>
</div>

<script type="text/javascript">
    
    class ProgressBar {
        static _value = 0;
        static _timer = null;
        static _last_changed = Date.now();
        static _hide_after_timeout_msec = 10 * 1000;
        static _hide_after_finished_msec = 1.5 * 1000;
        static _pbar = $('#compose_progress_bar.progress');
        static _pbar_progress = $('#compose_progress_bar .progress-bar');
        
        static _show() {
            this._pbar.css('display', 'block');
        }
        
        static _hide() {
            this._pbar.css('display', 'none');
        }
        
        static _hidden() {
            return this._pbar.css('display') === 'none';
        }
        
        static _timeit() {
            let _pbar = this;
            let w = function (){
                let age = Date.now() - _pbar._last_changed;
                if (age > _pbar._hide_after_timeout_msec || (_pbar._value >= 100 && age > _pbar._hide_after_finished_msec)) {
                    clearInterval(_pbar._timer);
                    _pbar._timer = null;
                    _pbar.clear();
                }
            };
            if (_pbar._timer === null){
                this._timer = setInterval(w, 500);
            }
        }
        
        static _set(progress) {
            this._value = Math.max(0, Math.min(100, progress));
            this._pbar_progress.css('width', '{0}%'.format(this._value));
            this._last_changed = Date.now();
            if (progress > 0 && progress < 100) {
                this._timeit();
            }
        }
        
        static _clear() {
            this._set(0);
        }
        
        static set(progress) {
            if (progress >= 0 && progress <= 100) {
                this._show();
                this._set(progress);
            } else if (progress > 100) {
                // do nothing
            } else {
                this._clear();
                this._hide();
            }
        }
        
        static clear() {
            this.set(-1);
        }
    }
    
</script>