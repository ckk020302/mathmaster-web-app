<div class="col-md-4 mb-4 classroom-col" data-classroom-id="{{ $classroom['id'] }}">
    <div class="card classroom-card h-100">
        <div class="card-header classroom-banner" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset($classroom['image'] ?? '/EXQq82JWkAAYtes.jpg') }}'); background-size: cover; background-position: center;">
            <h5 class="card-title">
                <a href="{{ route('classroom.show', $classroom['id']) }}" class="text-white">
                    {{ $classroom['name'] }}
                </a>
            </h5>
            <p class="card-teacher mb-0">{{ $classroom['teacher'] }}</p>
        </div>
        <div class="card-body">
            {{-- Optional content --}}
        </div>
    </div>
</div>
