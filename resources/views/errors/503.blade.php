@extends('frontend.default.layouts.app')

@section('content')
<section class="align-items-center d-flex h-100 bg-white">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mx-auto text-center py-4">
				<img src="{{ my_asset('assets/frontend/default/img/maintainance.svg') }}" class="img-fluid w-75">
			    <h3 class="fw-600 mt-5">{{translate('We are Under Maintenance.')}}</h3>
			    <div class="lead">{{translate('We will be back soon!')}}</div>
			</div>
		</div>
	</div>
</section>
@endsection
