@extends('layouts.app-layout')

@section('meta_title', 'Register Your Company | ' . seoSiteName())
@section('meta_robots', 'noindex, nofollow')

@section('extra-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        #register_form .select2-container {
            width: 100% !important;
        }
        #register_form .select2-selection--single {
            height: 42px;
            padding: 6px 8px;
            border: 1px solid #ced4da;
        }
        #register_form .select2-selection__rendered {
            line-height: 28px;
        }
        #register_form .select2-selection__arrow {
            height: 40px;
        }
        #new-company-hint {
            display: none;
            margin-top: 6px;
            font-size: 13px;
            color: #555;
        }
        #website_row.is-hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="sign-in-wrap">
        <div class="container">
            <div class="sing-in-box">

                <div class="join-comm-wrap" style="background-image: url(../images/login-content-bg-img.jpg);">
                </div>
                <div class="form-wrap">
                    <div>
                        <h3>Few steps ahead to join us</h3>
                        @if(session('social_auth_error'))
                            <p class="text-danger">{{ session('social_auth_error') }}</p>
                        @endif
                        @if(session('social_auth_info'))
                            <p class="text-info">{{ session('social_auth_info') }}</p>
                        @endif
                        @if(empty($oauthBuilderProfile))
                            <div class="social-media-wrap">
                                <ul>
                                    <li class="fb-icon">
                                        <a href="{{ route('socialite-facebook', ['intent' => 'builder_signup']) }}"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <h6>Or use your email for registration</h6>
                        @else
                            <p>Signing up with Facebook — complete your company details below.</p>
                        @endif
                        <form method="POST" id="register_form" action="{{route('save-builder')}}"
                              enctype="multipart/form-data"
                        >
                            @csrf
                            <input type="hidden" name="company_mode" id="company_mode"
                                   value="{{ old('company_mode', old('company_id') ? 'existing' : (old('company_name') ? 'new' : '')) }}">
                            <input type="hidden" name="company_name" id="company_name_hidden"
                                   value="{{ old('company_name') }}">

                            <div class="form-row">
                                <label for="company_id">Company Name</label>
                                <select id="company_id" name="company_id" class="w-100"
                                        data-placeholder="Search or add your company"
                                        data-old-id="{{ old('company_id') }}"
                                        data-old-name="{{ old('company_name') }}"
                                        data-old-mode="{{ old('company_mode', old('company_id') ? 'existing' : (old('company_name') ? 'new' : '')) }}">
                                    <option value=""></option>
                                </select>
                                <p id="new-company-hint">Creating a new company. Enter your website below to continue.</p>

                                <span class="text-danger">
                                    {{ $errors->first('company_name') ?: $errors->first('company_id') }}
                                    @if(($errors->first('company_name') || $errors->first('company_id'))
                                        && strpos(($errors->first('company_name') ?: $errors->first('company_id')), 'required') === false
                                        && strpos(($errors->first('company_name') ?: $errors->first('company_id')), 'Please select') === false)
                                        <a href="#" id="learn-more">Learn more</a>
                                    @endif
                                </span>
                            </div>
                            <div class="form-row">
                                <label for="email_form_element">Your Email</label>
                                <input type="text" id="email_form_element" name="email"
                                       value="{{ old('email', !empty($oauthBuilderProfile) ? $oauthBuilderProfile['email'] : '') }}"
                                       placeholder="johnsmith@gmail.com"
                                       @if(!empty($oauthBuilderProfile)) readonly @endif>
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                            <div class="form-row {{ old('company_mode', old('company_id') ? 'existing' : (old('company_name') ? 'new' : '')) === 'new' ? '' : 'is-hidden' }}"
                                 id="website_row">
                                <label for="website_form_element">Website Url</label>
                                <input type="text" id="website_form_element" name="website_url"
                                       value="{{old('website_url')}}"
                                       placeholder="https://www.ratemyestate.com">
                                <span class="text-danger">{{ $errors->first('website_url') }}</span>
                            </div>
                            @if(empty($oauthBuilderProfile))
                            <div class="form-row">
                                <label for="password_form_element">Password</label>
                                <input type="password" id="password_form_element" name="password" value=""
                                       placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                            </div>
                            <div class="form-row">
                                <label for="c_password_form_element">Confirm Password</label>
                                <input type="password" id="c_password_form_element" name="password_confirmation" value=""
                                       placeholder="6+ Characters">
                                <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                            </div>
                            @endif
                            <div class="form-row">
                                <label>Upload photo</label>
                                <div class="user-img-wrap">
                                    @if(session()->exists('company-logo'))
                                        <img id="session-image" class="image-preview" height="150px" width="200px"
                                             src={{session()->get('company-logo')}} alt='image'>
                                    @endif
                                    <img id="user-profile-image" class="image-preview">
                                </div>
                                <div class="upload-photo">
                                    <button href="javascript:;" class="btn yellow-btn">Upload Photo
                                        <input type='file'
                                               name="company_logo"
                                               class="btn yellow-btn"
                                               onchange="readURL(this);"
                                        /></button>
                                </div>
                                <span class="text-danger">{{ $errors->first('company_logo') }}</span>
                            </div>
                            <div class="form-row chkbox-wrap">
                                <label>
                                    <input type="checkbox" name="agreement">
                                    <span>I would like to opt for <strong><a
                                                href="{{route('privacy-policy')}}">Privacy Policy</a></strong> Marketing updates.</span>
                                </label>
                                <div><span class="text-danger">{{ $errors->first('agreement') }}</span></div>
                            </div>
                            <div id="review-form-field"></div>

                            <div class="form-row">
                                <input type="submit" id="submit_btn" value="Register Now">
                            </div>
                        </form>
                        <div>
                            <p>Already have an account ? Please <a href="{{ route('login', ['company' => 1]) }}">Log In</a></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        $(document).ready(function () {
            let APP_URL = {!! json_encode(url('/')) !!};
            let userSaved = {!! json_encode(session()->get('user_saved')) !!};
            let userSavedMode = {!! json_encode(session()->get('user_saved_mode')) !!};
            let messageText = userSavedMode === 'existing'
                ? '<p>Your access request has been submitted. You will be able to login once it is approved by admin.</p>'
                : '<p>Your company has been registered. You will be able to login once it is approved by admin.</p>';

            if (userSaved) {
                @php
                    session()->forget('user_saved');
                    session()->forget('user_saved_mode');
                @endphp
                Swal.fire({
                    title: 'Registered successfully',
                    html: messageText,
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Go To Homepage',
                    cancelButtonText: 'Go To Login'
                }).then(res => {
                    if (res['value']) {
                        window.location.href = APP_URL + '/';
                    }
                    if (res['dismiss'] && res['dismiss'] === 'cancel') {
                        window.location.href = APP_URL + '/login?company=1';
                    }
                });
            }

            const $companySelect = $('#company_id');
            const $companyMode = $('#company_mode');
            const $companyNameHidden = $('#company_name_hidden');
            const $websiteRow = $('#website_row');
            const $newCompanyHint = $('#new-company-hint');
            const NEW_COMPANY_PREFIX = 'new:';

            function setExistingMode(companyId, companyName) {
                $companyMode.val('existing');
                $companyNameHidden.val('');
                $websiteRow.addClass('is-hidden');
                $newCompanyHint.hide();
                if (companyId) {
                    const option = new Option(companyName, companyId, true, true);
                    $companySelect.find('option[value="' + companyId + '"]').remove();
                    $companySelect.append(option).trigger('change.select2');
                }
            }

            function setNewMode(companyName) {
                const value = NEW_COMPANY_PREFIX + companyName;
                $companyMode.val('new');
                $companyNameHidden.val(companyName);
                $websiteRow.removeClass('is-hidden');
                $newCompanyHint.show();
                $companySelect.find('option').filter(function () {
                    return String($(this).val()).indexOf(NEW_COMPANY_PREFIX) === 0;
                }).remove();
                const option = new Option(companyName + ' (new)', value, true, true);
                $companySelect.append(option).val(value).trigger('change.select2');
            }

            function clearCompanySelection() {
                $companyMode.val('');
                $companyNameHidden.val('');
                $websiteRow.addClass('is-hidden');
                $newCompanyHint.hide();
            }

            window.addCompanyFromRegister = function () {
                const searchInput = $companySelect.data('select2').$dropdown.find('.select2-search__field');
                const newCompany = (searchInput.val() || '').trim();
                if (!newCompany) {
                    return;
                }

                Swal.fire({
                    title: 'Create new company?',
                    text: 'Register "' + newCompany + '" as a new company. If it already exists, select it from the list instead.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.value) {
                        setNewMode(newCompany);
                    }
                });
            };

            $companySelect.select2({
                allowClear: true,
                placeholder: $companySelect.data('placeholder'),
                ajax: {
                    delay: 250,
                    url: "{{ route('autocomplete.fetchCompany') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: params.term || '',
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results || []
                        };
                    }
                },
                language: {
                    noResults: function () {
                        return "No results found <a href=\"javascript:void(0)\" onclick=\"addCompanyFromRegister()\">(add new company)</a>";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });

            $companySelect.on('select2:select', function (e) {
                const data = e.params.data || {};
                const id = String(data.id || '');
                if (id.indexOf(NEW_COMPANY_PREFIX) === 0) {
                    setNewMode(id.substring(NEW_COMPANY_PREFIX.length));
                    return;
                }
                setExistingMode(id, data.text || $(this).find('option:selected').text());
            });

            $companySelect.on('select2:clear', function () {
                clearCompanySelection();
                $companySelect.empty().append(new Option('', '', false, false));
            });

            // Restore previous selection after validation errors.
            const oldMode = $companySelect.data('old-mode');
            const oldId = $companySelect.data('old-id');
            const oldName = $companySelect.data('old-name');
            if (oldMode === 'existing' && oldId) {
                $.ajax({
                    url: "{{ route('autocomplete.fetchCompany') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: { q: oldName || '' }
                }).done(function (res) {
                    const match = (res.results || []).find(function (item) {
                        return String(item.id) === String(oldId);
                    });
                    const label = match ? match.text : (oldName || ('Company #' + oldId));
                    setExistingMode(oldId, label);
                }).fail(function () {
                    setExistingMode(oldId, oldName || ('Company #' + oldId));
                });
            } else if (oldMode === 'new' && oldName) {
                setNewMode(oldName);
            }

            $('#register_form').on('submit', function () {
                const mode = $companyMode.val();
                const selected = String($companySelect.val() || '');

                if (mode === 'new' || selected.indexOf(NEW_COMPANY_PREFIX) === 0) {
                    const name = $companyNameHidden.val()
                        || (selected.indexOf(NEW_COMPANY_PREFIX) === 0
                            ? selected.substring(NEW_COMPANY_PREFIX.length)
                            : '');
                    $companyMode.val('new');
                    $companyNameHidden.val(name);
                    // Do not post a fake company_id for create-new mode.
                    $companySelect.prop('disabled', true);
                    return true;
                }

                if (mode === 'existing' || (selected && selected.indexOf(NEW_COMPANY_PREFIX) !== 0)) {
                    $companyMode.val('existing');
                    $companyNameHidden.val('');
                    return true;
                }

                return true;
            });

            const learnMoreText = 'This company is already registered. Search and select it from the company list to request access. Your account will stay pending until an admin approves it.';
            $(document).on('click', '#learn-more', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Company already exists',
                    html: learnMoreText,
                    showCloseButton: true,
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
@endsection
