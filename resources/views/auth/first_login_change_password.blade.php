<x-auth>
    <form method="POST" action="{{ route('first-login.store') }}" class="ajax-form" id="loginform">
        @csrf
        <h3 class="text-capitalize mb-4 f-w-500">Change Password</h3>
        <p class="text-muted mb-4">First Login Change Password</p>

        <div class="form-group text-left">
            <label for="password">@lang('app.password')</label>
            <x-forms.input-group>
                <input type="password" name="password" id="password"
                       placeholder="@lang('placeholders.password')" tabindex="1"
                       class="form-control height-50 f-15 light_text @error('password') is-invalid @enderror">
                <x-slot name="append">
                    <button type="button"
                            class="btn btn-outline-secondary border-grey height-50 toggle-password">
                        <i class="fa fa-eye"></i>
                    </button>
                </x-slot>
            </x-forms.input-group>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group text-left">
            <label for="password_confirmation">@lang('app.confirmPassword')</label>
            <x-forms.input-group>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       placeholder="@lang('placeholders.password')" tabindex="2"
                       class="form-control height-50 f-15 light_text @error('password_confirmation') is-invalid @enderror">
                <x-slot name="append">
                    <button type="button"
                            class="btn btn-outline-secondary border-grey height-50 toggle-password">
                        <i class="fa fa-eye"></i>
                    </button>
                </x-slot>
            </x-forms.input-group>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" id="submit-login" class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            @lang('app.update') <i class="fa fa-arrow-right pl-1"></i>
        </button>
    </form>
    
    <x-slot name="scripts">
        <script>
            $(document).ready(function () {
                $('#loginform').submit(function (e) {
                    const pass = $('#password').val();
                    const regex = /^(?=.*[0-9])(?=.*[a-zA-Z])[a-zA-Z0-9]{8,16}$/;
                    if (!regex.test(pass)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            text: 'Password must be alphanumeric (contain both letters and numbers) and between 8 to 16 characters.',
                            showConfirmButton: true,
                            customClass: {
                                confirmButton: 'btn btn-primary',
                            },
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
                        });
                    }
                });

                // Password toggle functionality if not globally available
                $('.toggle-password').click(function () {
                    var input = $(this).closest('.input-group').find('input');
                    if (input.attr('type') == 'password') {
                        input.attr('type', 'text');
                        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                    } else {
                        input.attr('type', 'password');
                        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                });
            });
        </script>
    </x-slot>
</x-auth>
