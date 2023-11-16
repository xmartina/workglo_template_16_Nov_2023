<!-- Iyzico -->
<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate("Iyzico Configuration")}}</h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('payment-config.store') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="payment_method" value="iyzico ">
                <div class="form-group">
                    <div class="row">
                        <div class="col-2">
                            <label class="align-self-center" for="rtl">{{translate('Activation')}}</label>
                        </div>
                        <div class="col-4">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" @if(\App\Utility\SettingsUtility::get_settings_value('iyzico_activation_checkbox') == 1) checked @endif name="iyzico_activation_checkbox">
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="types">{{translate('API Key')}}</label>
                    <input type="hidden" name="types[]" value="IYZICO_API_KEY">
                    <input type="text" name="IYZICO_API_KEY" class="form-control" value="{{env('IYZICO_API_KEY')}}">
                </div>
                <div class="form-group mb-3">
                    <label for="types">{{translate('Secret Key')}}</label>
                    <input type="hidden" name="types[]" value="IYZICO_SECRET_KEY">
                    <input type="text" name="IYZICO_SECRET_KEY" class="form-control" value="{{env('IYZICO_SECRET_KEY')}}">
                </div>
                <div class="form-group mb-3">
                    <label for="types">{{translate('Currency Code')}}</label>
                    <input type="hidden" name="types[]" value="IYZICO_CURRENCY_CODE">
                    <input type="text" name="IYZICO_CURRENCY_CODE" class="form-control" value="{{env('IYZICO_CURRENCY_CODE')}}">
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <label class="align-self-center" for="rtl">{{translate('IYZICO Sandbox Mode')}}</label>
                        </div>
                        <div class="col-4">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" @if(\App\Utility\SettingsUtility::get_settings_value('iyzico_sandbox_checkbox') == 1) checked @endif name="iyzico_sandbox_checkbox">
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>