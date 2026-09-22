/**
 * Linked State / Suburb / Postcode selection, backed by /api/localities.
 *
 * Location used to be captured as free text, which is what broke the admin
 * filters. Every submission now carries a locality_id resolved from the
 * reference table; the server rejects a combination that does not resolve.
 *
 * Usage:
 *   var locality = RmeLocalitySelect.init({
 *       stateEl: '#state_id',
 *       stateValueEl: '#state_hidden',
 *       suburbEl: '#suburb',
 *       suburbValueEl: '#suburb_name',
 *       postcodeEl: '#post_code',
 *       localityIdEl: '#locality_id',
 *       initial: { stateId: '3', suburb: 'Ascot', postcode: '4007' }
 *   });
 */
(function (window, $) {
    'use strict';

    var SEARCH_DEBOUNCE_MS = 200;
    var MIN_SEARCH_LENGTH = 2;
    var POSTCODE_PATTERN = /^\d{4}$/;

    /**
     * Matching form of a suburb name. Must stay in step with
     * App\Models\Suburbs::normalise() so the client and server agree.
     */
    function normalise(name) {
        return String(name == null ? '' : name)
            .toLowerCase()
            .replace(/['\u2019`]/g, '')
            .replace(/[^a-z0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function Controller(options) {
        this.options = options || {};
        this.apiBase = (this.options.apiBase || '/api') + '/localities';

        this.$state = $(this.options.stateEl);
        this.$stateValue = this.options.stateValueEl ? $(this.options.stateValueEl) : $();
        this.$suburb = $(this.options.suburbEl);
        this.$suburbValue = this.options.suburbValueEl ? $(this.options.suburbValueEl) : $();
        this.$postcode = $(this.options.postcodeEl);
        this.$localityId = $(this.options.localityIdEl);

        this.states = [];
        this.stateCodeById = {};
        this.localities = [];
        this.stateCode = '';
        this.suburbName = '';
        this.locked = false;
        this.suppressStateClear = false;
        this.postcodeTimer = null;
        this.pendingInitial = this.options.initial || {};

        this.buildPostcodeControls();
        this.bindPostcode();
        this.loadStates();
    }

    /**
     * The postcode field submits from a single input. A sibling select appears
     * when a suburb spans several postcodes, and an override link unlocks the
     * input when it does not.
     */
    Controller.prototype.buildPostcodeControls = function () {
        var self = this;

        this.$postcodeOptions = $('<select class="rme-locality-postcodes w-100" style="display:none;"></select>');
        this.$override = $('<button type="button" class="rme-locality-override"' +
            ' style="display:none;background:none;border:0;padding:0;margin-top:4px;' +
            'font-size:12px;text-decoration:underline;cursor:pointer;">Enter a different postcode</button>');

        this.$postcode.after(this.$override);
        this.$postcode.after(this.$postcodeOptions);

        this.$postcodeOptions.on('change', function () {
            self.$postcode.val($(this).val() || '');
            self.resolve();
        });

        this.$override.on('click', function () {
            self.$postcode.prop('readonly', false).focus();
            $(this).hide();
        });
    };

    Controller.prototype.bindPostcode = function () {
        var self = this;

        // FR-6: a directly typed postcode drives state and suburb.
        this.$postcode.on('input', function () {
            if (self.locked) {
                return;
            }
            window.clearTimeout(self.postcodeTimer);
            self.postcodeTimer = window.setTimeout(function () {
                self.onPostcodeTyped();
            }, SEARCH_DEBOUNCE_MS);
        });
    };

    Controller.prototype.loadStates = function () {
        var self = this;

        $.ajax({ url: this.apiBase + '/states', method: 'GET', dataType: 'json' })
            .done(function (response) {
                self.states = (response && response.data) || [];
                self.stateCodeById = {};

                var data = self.states.map(function (state) {
                    self.stateCodeById[String(state.id)] = state.code;
                    return { id: String(state.id), text: state.name };
                });

                self.$state.select2({
                    allowClear: true,
                    placeholder: 'Select State',
                    data: data
                });

                self.$state.on('change', function () {
                    self.onStateChange($(this).val());
                });

                self.applyInitial();
            })
            .fail(function () {
                self.initSuburbSelect('Unable to load states', true);
            });
    };

    Controller.prototype.applyInitial = function () {
        var initial = this.pendingInitial || {};
        var stateId = initial.stateId ? String(initial.stateId) : '';

        if (!stateId) {
            this.$state.val(null).trigger('change');
            this.initSuburbSelect('Select state first', true);
            return;
        }

        // Restoring a previous answer must not wipe the suburb and postcode.
        this.suppressStateClear = true;
        this.$state.val(stateId).trigger('change');
        this.suppressStateClear = false;

        this.loadLocalities(this.stateCodeById[stateId], initial.suburb, initial.postcode);
    };

    Controller.prototype.onStateChange = function (stateId) {
        var code = this.stateCodeById[String(stateId)] || '';
        this.stateCode = code;
        this.$stateValue.val(stateId || '');

        if (this.suppressStateClear) {
            return;
        }

        // FR-8: changing state clears suburb and postcode.
        this.clearSuburbAndPostcode();

        if (!code) {
            this.initSuburbSelect('Select state first', true);
            return;
        }

        this.loadLocalities(code);
    };

    Controller.prototype.clearSuburbAndPostcode = function () {
        this.suburbName = '';
        this.$suburbValue.val('');
        this.$postcode.val('').prop('readonly', true);
        this.$postcodeOptions.hide().empty();
        this.$override.hide();
        this.$localityId.val('');
    };

    /**
     * Fetch every active locality in the state. One request per state change
     * keeps the dropdown complete and alphabetical without paging.
     */
    Controller.prototype.loadLocalities = function (stateCode, preselectSuburb, preselectPostcode) {
        var self = this;

        if (!stateCode) {
            this.localities = [];
            this.initSuburbSelect('Select state first', true);
            return;
        }

        this.initSuburbSelect('Loading suburbs…', true);

        $.ajax({
            url: this.apiBase,
            method: 'GET',
            dataType: 'json',
            data: { state: stateCode }
        })
            .done(function (response) {
                self.localities = (response && response.data) || [];
                self.initSuburbSelect('Select Suburb', false);

                if (preselectSuburb) {
                    self.selectSuburb(preselectSuburb, preselectPostcode);
                }
                self.applyLockState();
            })
            .fail(function () {
                self.localities = [];
                self.initSuburbSelect('Select Suburb', false);
            });
    };

    /**
     * Distinct suburb names for the current state, alphabetical. A name that
     * repeats inside the state is labelled with its postcodes so the two are
     * told apart before the postcode field is touched.
     */
    Controller.prototype.suburbOptions = function (localities) {
        var byName = {};
        var order = [];

        (localities || []).forEach(function (row) {
            var key = normalise(row.suburb);
            if (!byName[key]) {
                byName[key] = { name: row.suburb, postcodes: [] };
                order.push(key);
            }
            if (byName[key].postcodes.indexOf(row.postcode) === -1) {
                byName[key].postcodes.push(row.postcode);
            }
        });

        return order.map(function (key) {
            var entry = byName[key];
            return {
                id: key,
                text: entry.postcodes.length > 1
                    ? entry.name + ' (' + entry.postcodes.join(', ') + ')'
                    : entry.name,
                name: entry.name
            };
        }).sort(function (a, b) {
            return a.text.localeCompare(b.text);
        });
    };

    Controller.prototype.initSuburbSelect = function (placeholder, disabled) {
        var self = this;

        if (this.$suburb.hasClass('select2-hidden-accessible')) {
            this.$suburb.select2('destroy');
        }
        this.$suburb.off('select2:select.rmeLocality select2:clear.rmeLocality');
        this.$suburb.empty().append(new Option('', '', false, false));

        this.$suburb.select2({
            allowClear: true,
            placeholder: placeholder,
            minimumInputLength: 0,
            ajax: {
                delay: SEARCH_DEBOUNCE_MS,
                transport: function (params, success, failure) {
                    return self.suburbTransport(params, success, failure);
                },
                processResults: function (data) {
                    return { results: data.results };
                }
            },
            language: {
                noResults: function () {
                    return 'No Results Found';
                },
                inputTooShort: function () {
                    return 'Keep typing to search';
                }
            }
        });

        this.$suburb.prop('disabled', !!disabled);

        this.$suburb.on('select2:select.rmeLocality', function (event) {
            var selected = event.params.data || {};
            self.onSuburbSelected(selected.name || selected.text || '');
        });

        this.$suburb.on('select2:clear.rmeLocality', function () {
            self.clearSuburbAndPostcode();
        });
    };

    /**
     * Below the search threshold the complete state list is served from memory,
     * so opening the dropdown always shows every suburb. From two characters up
     * the request goes to the debounced search endpoint.
     */
    Controller.prototype.suburbTransport = function (params, success, failure) {
        var self = this;
        var term = (params.data && params.data.term) ? String(params.data.term) : '';

        if (normalise(term).length < MIN_SEARCH_LENGTH) {
            success({ results: this.suburbOptions(this.localities) });
            return { abort: function () {} };
        }

        return $.ajax({
            url: this.apiBase + '/search',
            method: 'GET',
            dataType: 'json',
            data: { state: this.stateCode, q: term }
        })
            .done(function (response) {
                success({ results: self.suburbOptions((response && response.data) || []) });
            })
            .fail(failure);
    };

    /**
     * Localities in the current state whose name matches, in postcode order.
     */
    Controller.prototype.matchesFor = function (suburbName) {
        var key = normalise(suburbName);

        return this.localities.filter(function (row) {
            return normalise(row.suburb) === key;
        }).sort(function (a, b) {
            return String(a.postcode).localeCompare(String(b.postcode));
        });
    };

    Controller.prototype.onSuburbSelected = function (suburbName) {
        var matches = this.matchesFor(suburbName);

        if (!matches.length) {
            this.clearSuburbAndPostcode();
            return;
        }

        this.suburbName = matches[0].suburb;
        this.$suburbValue.val(this.suburbName);

        if (matches.length === 1) {
            // FR-5: single postcode is filled and read-only, with an override.
            this.$postcodeOptions.hide().empty();
            this.$postcode.val(matches[0].postcode).prop('readonly', true).show();
            this.$override.show();
            this.resolve();
            return;
        }

        // FR-5: several postcodes are offered with no default pre-selected.
        this.$override.hide();
        this.$postcode.val('').prop('readonly', true);
        this.$postcodeOptions.empty().append(new Option('Select Postcode', '', true, true));
        matches.forEach(function (row) {
            this.$postcodeOptions.append(new Option(row.postcode, row.postcode, false, false));
        }, this);
        this.$postcodeOptions.show();
        this.$localityId.val('');
    };

    /**
     * FR-6: four valid digits set the state and narrow the suburb list to the
     * localities sharing that postcode. A suburb is only auto-selected when the
     * postcode leaves exactly one option.
     */
    Controller.prototype.onPostcodeTyped = function () {
        var self = this;
        var postcode = String(this.$postcode.val() || '').trim();

        if (!POSTCODE_PATTERN.test(postcode)) {
            this.$localityId.val('');
            return;
        }

        $.ajax({
            url: this.apiBase + '/by-postcode',
            method: 'GET',
            dataType: 'json',
            data: { postcode: postcode }
        })
            .done(function (response) {
                var rows = (response && response.data) || [];
                if (!rows.length) {
                    self.$localityId.val('');
                    return;
                }

                var stateCode = rows[0].state_code;
                if (stateCode && stateCode !== self.stateCode) {
                    self.setStateByCode(stateCode, function () {
                        self.applyPostcodeMatches(rows, postcode);
                    });
                    return;
                }

                self.applyPostcodeMatches(rows, postcode);
            })
            .fail(function () {
                self.$localityId.val('');
            });
    };

    Controller.prototype.applyPostcodeMatches = function (rows, postcode) {
        var inState = rows.filter(function (row) {
            return !this.stateCode || row.state_code === this.stateCode;
        }, this);

        var candidates = inState.length ? inState : rows;

        if (candidates.length === 1) {
            this.selectSuburb(candidates[0].suburb, postcode);
            return;
        }

        // Several suburbs share the postcode — narrow the list, select nothing.
        this.$suburb.empty().append(new Option('', '', false, false));
        this.suburbOptions(candidates).forEach(function (option) {
            this.$suburb.append(new Option(option.text, option.id, false, false));
        }, this);
        this.$suburb.val(null).trigger('change.select2');
        this.$suburbValue.val('');
        this.$localityId.val('');
    };

    Controller.prototype.setStateByCode = function (stateCode, done) {
        var stateId = '';

        for (var id in this.stateCodeById) {
            if (Object.prototype.hasOwnProperty.call(this.stateCodeById, id)
                && this.stateCodeById[id] === stateCode) {
                stateId = id;
                break;
            }
        }

        if (!stateId) {
            return;
        }

        var self = this;
        this.suppressStateClear = true;
        this.$state.val(stateId).trigger('change');
        this.suppressStateClear = false;
        this.stateCode = stateCode;
        this.$stateValue.val(stateId);

        $.ajax({
            url: this.apiBase,
            method: 'GET',
            dataType: 'json',
            data: { state: stateCode }
        })
            .done(function (response) {
                self.localities = (response && response.data) || [];
                self.initSuburbSelect('Select Suburb', false);
                if (typeof done === 'function') {
                    done();
                }
            });
    };

    /**
     * Select a suburb programmatically — restoring a saved answer, applying a
     * property's location, or resolving a typed postcode.
     */
    Controller.prototype.selectSuburb = function (suburbName, postcode) {
        var matches = this.matchesFor(suburbName);

        if (!matches.length) {
            // Legacy free-text value with no locality row. Show it so the user
            // sees what was stored, but leave locality_id empty so the server
            // blocks the submission until a real locality is picked.
            this.$suburb.append(new Option(suburbName, normalise(suburbName), true, true)).trigger('change.select2');
            this.$suburbValue.val(suburbName);
            if (postcode) {
                this.$postcode.val(postcode);
            }
            this.$localityId.val('');
            return;
        }

        this.$suburb.val(normalise(matches[0].suburb));
        if (!this.$suburb.find('option[value="' + normalise(matches[0].suburb) + '"]').length) {
            this.$suburb.append(new Option(matches[0].suburb, normalise(matches[0].suburb), true, true));
        }
        this.$suburb.trigger('change.select2');

        this.onSuburbSelected(matches[0].suburb);

        if (postcode && matches.length > 1) {
            this.$postcodeOptions.val(postcode);
            this.$postcode.val(postcode);
            this.resolve();
        }
    };

    /**
     * FR-7: write locality_id only when state, suburb and postcode identify
     * exactly one record. Anything else leaves it empty and the server rejects.
     */
    Controller.prototype.resolve = function () {
        var suburbKey = normalise(this.$suburbValue.val());
        var postcode = String(this.$postcode.val() || '').trim();
        var stateCode = this.stateCode;

        if (!suburbKey || !POSTCODE_PATTERN.test(postcode) || !stateCode) {
            this.$localityId.val('');
            return null;
        }

        var matches = this.localities.filter(function (row) {
            return normalise(row.suburb) === suburbKey
                && String(row.postcode) === postcode
                && row.state_code === stateCode;
        });

        if (matches.length !== 1) {
            this.$localityId.val('');
            return null;
        }

        this.$localityId.val(matches[0].id);
        return matches[0];
    };

    /**
     * Lock the fields when the location comes from the selected property.
     */
    Controller.prototype.setLocked = function (locked) {
        this.locked = !!locked;
        this.applyLockState();
    };

    Controller.prototype.applyLockState = function () {
        this.$state.prop('disabled', this.locked);
        this.$suburb.prop('disabled', this.locked || !this.stateCode);
        this.$postcodeOptions.prop('disabled', this.locked);
        if (this.locked) {
            this.$postcode.prop('readonly', true);
            this.$override.hide();
        }
    };

    /**
     * Apply a property's stored location, then lock the three fields.
     *
     * @param {{state_id: *, suburb: string, post_code: string}} property
     */
    Controller.prototype.applyFromProperty = function (property) {
        if (!property || !property.suburb) {
            this.setLocked(false);
            return;
        }

        var stateId = property.state_id ? String(property.state_id) : '';
        var stateCode = this.stateCodeById[stateId];

        if (!stateCode) {
            this.setLocked(false);
            return;
        }

        var self = this;
        this.setLocked(false);
        this.suppressStateClear = true;
        this.$state.val(stateId).trigger('change');
        this.suppressStateClear = false;
        this.stateCode = stateCode;
        this.$stateValue.val(stateId);

        this.loadLocalities(stateCode, property.suburb, property.post_code);
        window.setTimeout(function () {
            self.setLocked(true);
        }, 0);
    };

    window.RmeLocalitySelect = {
        init: function (options) {
            return new Controller(options);
        },
        normalise: normalise
    };
})(window, jQuery);
