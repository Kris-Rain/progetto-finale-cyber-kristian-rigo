<x-layout>
    <div class="container my-5">
        <h1>Profilo</h1>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
              <label class="form-label fw-semibold">Name</label>
              <input class="form-control" type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                placeholder="You really change your name?!">
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Email</label>
              <input class="form-control" type="email" name="email" placeholder="Your brand new email"
                value="{{ old('email', auth()->user()->email) }}">
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Nuova password</label>
              <input class="form-control" type="password" name="password" placeholder="Your brand new password">
            </div>

            <button class="btn btn-primary">Salva</button>
        </form>
    </div>
</x-layout>