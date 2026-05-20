(function (global) {
    'use strict';

    function getConfig() {
        return global.ConnexLoginConfig || {};
    }

    function getPath(obj, path) {
        if (!path) {
            return undefined;
        }
        return path.split('.').reduce(function (current, key) {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }

    function $(id) {
        return id ? document.getElementById(id) : null;
    }

    function ConnexLogin() {
        this.cfg = getConfig();
        this.sel = this.cfg.selectors || {};
        this.accessToken = null;
        this.protectionReady = false;
        this.gatewayTimeoutId = null;
        this.gatewayLoadHandler = null;
        this.loginPhase = 'phone';
        this.pendingMsisdn = null;
    }

    ConnexLogin.prototype.appendLog = function (level, message, detail) {
        if (!this.cfg.debugLog) {
            return;
        }
        var el = $(this.sel.log_output);
        if (!el) {
            return;
        }
        var ts = new Date().toISOString();
        var line = '[' + ts + '] [' + level + '] ' + message;
        if (detail !== undefined) {
            line += '\n' + (typeof detail === 'string' ? detail : JSON.stringify(detail, null, 2));
        }
        el.textContent = (el.textContent ? el.textContent + '\n\n' : '') + line;
        el.scrollTop = el.scrollHeight;
    };

    ConnexLogin.prototype.parseJsonResponse = function (response, text) {
        var data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            data = text;
        }
        return { response: response, data: data };
    };

    ConnexLogin.prototype.failedMessage = function (data) {
        if (typeof data === 'string') {
            return data;
        }
        return (data.failed && data.failed.message) || data.message || 'request failed';
    };

    ConnexLogin.prototype.bearerHeaders = function (contentType) {
        var headers = {
            Authorization: 'Bearer ' + this.accessToken,
            Accept: 'application/json',
        };
        if (contentType) {
            headers['Content-Type'] = contentType;
        }
        return headers;
    };

    ConnexLogin.prototype.fetchAccessToken = function () {
        var self = this;
        var payload = {};
        payload[this.cfg.authEmailField] = this.cfg.authEmail;
        payload[this.cfg.authPasswordField] = this.cfg.authPassword;

        this.appendLog('INFO', 'POST ' + this.cfg.authLoginUrl, { body: payload });

        return fetch(this.cfg.authLoginUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(payload),
        }).then(function (response) {
            return response.text().then(function (text) {
                var parsed = self.parseJsonResponse(response, text);
                self.appendLog(response.ok ? 'OK' : 'HTTP ' + response.status, 'auth-login response', parsed.data);

                if (!response.ok) {
                    throw new Error('auth-login HTTP ' + response.status);
                }

                var token = getPath(parsed.data, self.cfg.tokenJsonPath || 'data.access_token');
                if (parsed.data.messageCode !== '00' || !token) {
                    throw new Error('auth-login failed: ' + self.failedMessage(parsed.data));
                }

                self.accessToken = token;
                self.appendLog('OK', 'Bearer token acquired');
                return self.accessToken;
            });
        });
    };

    ConnexLogin.prototype.clearGatewayTimeout = function () {
        if (this.gatewayTimeoutId !== null) {
            clearTimeout(this.gatewayTimeoutId);
            this.gatewayTimeoutId = null;
        }
    };

    ConnexLogin.prototype.unbindGatewayLoad = function () {
        if (this.gatewayLoadHandler) {
            document.removeEventListener('gateway-load', this.gatewayLoadHandler);
            this.gatewayLoadHandler = null;
        }
    };

    ConnexLogin.prototype.waitForGatewayLoad = function () {
        var self = this;
        var timeoutMs = this.cfg.gatewayLoadTimeoutMs || 15000;

        return new Promise(function (resolve) {
            self.gatewayLoadHandler = function () {
                self.clearGatewayTimeout();
                self.unbindGatewayLoad();
                self.appendLog('OK', 'gateway-load — antifraud ready');
                self.setSignInEnabled(true);
                resolve();
            };

            document.addEventListener('gateway-load', self.gatewayLoadHandler);

            self.gatewayTimeoutId = setTimeout(function () {
                self.gatewayTimeoutId = null;
                self.unbindGatewayLoad();
                self.appendLog('WARN', 'gateway-load timeout (' + timeoutMs + 'ms) — enabling Sign In anyway');
                self.setSignInEnabled(true);
                resolve();
            }, timeoutMs);

            self.appendLog('INFO', 'Waiting for gateway-load (max ' + timeoutMs + 'ms)');
        });
    };

    ConnexLogin.prototype.runDcbProtect = function (scriptText) {
        var old = document.getElementById('dcbprotect-runtime');
        if (old) {
            old.remove();
        }
        var tag = document.createElement('script');
        tag.id = 'dcbprotect-runtime';
        tag.text = scriptText;
        document.body.appendChild(tag);
        document.dispatchEvent(new Event('DCBProtectRun'));
        this.appendLog('INFO', 'DCBProtectRun dispatched');
    };

    ConnexLogin.prototype.setSignInEnabled = function (enabled) {
        var btn = $(this.sel.submit_button);
        if (!btn) {
            return;
        }
        this.protectionReady = enabled;
        btn.disabled = !enabled;
        if (enabled) {
            btn.textContent = btn.dataset.connexLabelSignIn || 'Sign In';
            if (btn.dataset.connexEnabledClass) {
                btn.className = btn.dataset.connexEnabledClass;
            }
        } else {
            btn.textContent = btn.dataset.connexLabelLoading || 'Loading…';
            if (btn.dataset.connexDisabledClass) {
                btn.className = btn.dataset.connexDisabledClass;
            }
        }
    };

    ConnexLogin.prototype.loadProtectedScript = function () {
        var self = this;
        var targeted = this.cfg.targetedElement || ('#' + (this.sel.submit_button || 'cta_button'));
        var url = this.cfg.protectedScriptUrl + '?targeted_element=' + encodeURIComponent(targeted);

        this.appendLog('INFO', 'GET ' + url);

        return fetch(url, {
            method: 'GET',
            headers: this.bearerHeaders(),
        }).then(function (response) {
            return response.text().then(function (text) {
                var parsed = self.parseJsonResponse(response, text);
                self.appendLog(response.ok ? 'OK' : 'HTTP ' + response.status, 'protected-script response', parsed.data);

                if (!response.ok) {
                    throw new Error('protected-script HTTP ' + response.status);
                }

                if (parsed.data.messageCode !== '00' || !parsed.data.success) {
                    throw new Error('protected-script failed: ' + self.failedMessage(parsed.data));
                }

                var success = parsed.data.success;
                var transactionId = success.transaction_identify;
                var dcbprotect = success.dcbprotect;

                if (!transactionId || !dcbprotect) {
                    throw new Error('protected-script missing transaction_identify or dcbprotect');
                }

                var txInput = $(self.sel.transaction_identify);
                if (txInput) {
                    txInput.value = transactionId;
                }

                self.runDcbProtect(dcbprotect);

                self.appendLog('OK', 'Protection loaded', {
                    transaction_identify: transactionId,
                    targeted_element: targeted,
                    message: success.message,
                    user: success.user,
                });

                return self.waitForGatewayLoad().then(function () {
                    return success;
                });
            });
        });
    };

    ConnexLogin.prototype.showOtpStep = function (msisdn, otpMessage) {
        this.loginPhase = 'otp';
        this.pendingMsisdn = msisdn;

        var phoneStep = $(this.sel.phone_step);
        var otpStep = $(this.sel.otp_step);
        if (phoneStep) {
            phoneStep.classList.add('hidden');
        }
        if (otpStep) {
            otpStep.classList.remove('hidden');
        }

        var hint = document.getElementById('connex_otp_hint');
        if (hint) {
            hint.textContent = otpMessage || 'Enter the OTP sent to your phone.';
        }

        var btn = $(this.sel.submit_button);
        if (btn) {
            btn.textContent = btn.dataset.connexLabelVerifyOtp || 'Verify OTP';
            btn.disabled = false;
        }

        var otpEl = $(this.sel.otp);
        if (otpEl) {
            otpEl.value = '';
            otpEl.focus();
        }
    };

    ConnexLogin.prototype.submitLoginConnex = function () {
        var self = this;
        var msisdnEl = $(this.sel.msisdn);
        var txEl = $(this.sel.transaction_identify);
        var deviceInput = document.querySelector('[name="' + (this.sel.device_type || 'device_type') + '"]');

        if (!this.accessToken) {
            this.appendLog('WARN', 'No bearer token — run auth-login first');
            return Promise.resolve();
        }

        if (!this.protectionReady || !txEl || !txEl.value) {
            this.appendLog('WARN', 'Wait for protection script to finish loading');
            return Promise.resolve();
        }

        var msisdn = msisdnEl ? msisdnEl.value.trim() : '';
        if (!msisdn) {
            this.appendLog('WARN', 'MSISDN is required before submit');
            return Promise.resolve();
        }

        var body = new URLSearchParams({
            msisdn: msisdn,
            transaction_identify: txEl.value,
            device_type: deviceInput ? deviceInput.value : (this.cfg.deviceType || 'web'),
        }).toString();

        this.appendLog('INFO', 'POST ' + this.cfg.loginUrl, { body: Object.fromEntries(new URLSearchParams(body)) });

        return fetch(this.cfg.loginUrl, {
            method: 'POST',
            headers: this.bearerHeaders('application/x-www-form-urlencoded'),
            body: body,
        }).then(function (response) {
            return response.text().then(function (text) {
                var parsed = self.parseJsonResponse(response, text);
                self.appendLog(
                    response.ok && parsed.data.messageCode === '00' ? 'OK' : 'HTTP ' + response.status,
                    'login-connex response',
                    parsed.data
                );

                if (parsed.data.messageCode === '00' && parsed.data.success) {
                    self.showOtpStep(msisdn, parsed.data.success.message || 'OTP sent');
                }

                return parsed.data;
            });
        });
    };

    ConnexLogin.prototype.submitConfirmOtp = function () {
        var self = this;
        var otpEl = $(this.sel.otp);
        var otp = otpEl ? otpEl.value.trim() : '';

        if (!this.pendingMsisdn) {
            this.appendLog('WARN', 'Request OTP first');
            return Promise.resolve();
        }

        if (!otp) {
            this.appendLog('WARN', 'OTP is required');
            return Promise.resolve();
        }

        var payload = { msisdn: this.pendingMsisdn, otp: otp };
        this.appendLog('INFO', 'POST ' + this.cfg.confirmOtpUrl, payload);

        return fetch(this.cfg.confirmOtpUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.cfg.csrfToken || '',
            },
            body: JSON.stringify(payload),
        }).then(function (response) {
            return response.text().then(function (text) {
                var parsed = self.parseJsonResponse(response, text);
                self.appendLog(
                    response.ok && parsed.data.messageCode === '00' ? 'OK' : 'HTTP ' + response.status,
                    'confirm-otp response',
                    parsed.data
                );

                if (parsed.data.messageCode === '00') {
                    document.dispatchEvent(new CustomEvent('connex:authenticated', { detail: parsed.data }));
                    if (typeof self.cfg.onAuthSuccess === 'function') {
                        self.cfg.onAuthSuccess(parsed.data);
                    }
                }

                return parsed.data;
            });
        });
    };

    ConnexLogin.prototype.bindSubmit = function () {
        var self = this;
        var btn = $(this.sel.submit_button);
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function () {
            btn.disabled = true;

            var action = self.loginPhase === 'otp'
                ? self.submitConfirmOtp()
                : self.submitLoginConnex();

            if (self.loginPhase !== 'otp') {
                btn.textContent = btn.dataset.connexLabelSigningIn || 'Sending OTP…';
            } else {
                btn.textContent = btn.dataset.connexLabelSigningIn || 'Verifying…';
            }

            action
                .catch(function (error) {
                    self.appendLog('ERROR', 'Submit failed', error.message || String(error));
                })
                .finally(function () {
                    if (self.protectionReady || self.loginPhase === 'otp') {
                        btn.disabled = false;
                        btn.textContent = self.loginPhase === 'otp'
                            ? (btn.dataset.connexLabelVerifyOtp || 'Verify OTP')
                            : (btn.dataset.connexLabelSignIn || 'Sign In');
                    }
                });
        });
    };

    ConnexLogin.prototype.bindLogClear = function () {
        var self = this;
        var clearBtn = $(this.sel.log_clear);
        if (!clearBtn) {
            return;
        }
        clearBtn.addEventListener('click', function () {
            var el = $(self.sel.log_output);
            if (el) {
                el.textContent = '';
            }
            self.appendLog('INFO', 'Log cleared');
        });
    };

    ConnexLogin.prototype.bootstrap = function () {
        var self = this;
        this.clearGatewayTimeout();
        this.unbindGatewayLoad();
        this.setSignInEnabled(false);

        return this.fetchAccessToken()
            .then(function () {
                return self.loadProtectedScript();
            })
            .catch(function (error) {
                self.clearGatewayTimeout();
                self.unbindGatewayLoad();
                self.appendLog('ERROR', 'Bootstrap failed', error.message || String(error));
                self.setSignInEnabled(false);
                throw error;
            });
    };

    ConnexLogin.prototype.init = function () {
        var self = this;
        this.appendLog('INFO', 'Page ready — auth-login → protected-script');
        this.bindLogClear();
        this.bindSubmit();
        return this.bootstrap();
    };

    global.ConnexLogin = ConnexLogin;

    document.addEventListener('DOMContentLoaded', function () {
        if (!global.ConnexLoginConfig) {
            console.error('[connex] ConnexLoginConfig is missing. Include @include("connex::partials.config") before scripts.');
            return;
        }
        global.connexLoginInstance = new ConnexLogin();
        global.connexLoginInstance.init();
    });
})(window);
