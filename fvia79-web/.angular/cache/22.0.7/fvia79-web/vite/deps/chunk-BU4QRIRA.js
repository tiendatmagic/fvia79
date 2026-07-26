import {
  DOCUMENT,
  Injectable,
  InjectionToken,
  Service,
  inject,
  setClassMetadata,
  ɵɵdefineInjectable,
  ɵɵdefineService,
  ɵɵinject
} from "./chunk-JTAU332Q.js";
import {
  __publicField
} from "./chunk-DPTKPNMV.js";

// node_modules/@angular/common/fesm2022/_xhr-chunk.mjs
function parseCookieValue(cookieStr, name) {
  name = encodeURIComponent(name);
  for (const cookie of cookieStr.split(";")) {
    const eqIndex = cookie.indexOf("=");
    const [cookieName, cookieValue] = eqIndex == -1 ? [cookie, ""] : [cookie.slice(0, eqIndex), cookie.slice(eqIndex + 1)];
    if (cookieName.trim() === name) {
      return decodeURIComponent(cookieValue);
    }
  }
  return null;
}
var _BrowserXhr = class _BrowserXhr {
  build() {
    return new XMLHttpRequest();
  }
};
__publicField(_BrowserXhr, "ɵfac", function BrowserXhr_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _BrowserXhr)();
});
__publicField(_BrowserXhr, "ɵprov", ɵɵdefineService({
  token: _BrowserXhr,
  factory: _BrowserXhr.ɵfac
}));
var BrowserXhr = _BrowserXhr;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(BrowserXhr, [{
    type: Service
  }], null, null);
})();
var _XhrFactory = class _XhrFactory {
};
__publicField(_XhrFactory, "ɵfac", function XhrFactory_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _XhrFactory)();
});
__publicField(_XhrFactory, "ɵprov", ɵɵdefineInjectable({
  token: _XhrFactory,
  factory: function XhrFactory_Factory(__ngFactoryType__) {
    let __ngConditionalFactory__ = null;
    if (__ngFactoryType__) {
      __ngConditionalFactory__ = new (__ngFactoryType__ || _XhrFactory)();
    } else {
      __ngConditionalFactory__ = ɵɵinject(BrowserXhr);
    }
    return __ngConditionalFactory__;
  },
  providedIn: "root"
}));
var XhrFactory = _XhrFactory;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(XhrFactory, [{
    type: Injectable,
    args: [{
      providedIn: "root",
      useExisting: BrowserXhr
    }]
  }], null, null);
})();

// node_modules/@angular/common/fesm2022/_platform_location-chunk.mjs
var _DOM = null;
function getDOM() {
  return _DOM;
}
function setRootDomAdapter(adapter) {
  _DOM != null ? _DOM : _DOM = adapter;
}
var DomAdapter = class {
};
var _PlatformLocation = class _PlatformLocation {
  historyGo(relativePosition) {
    throw new Error(ngDevMode ? "Not implemented" : "");
  }
};
__publicField(_PlatformLocation, "ɵfac", function PlatformLocation_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _PlatformLocation)();
});
__publicField(_PlatformLocation, "ɵprov", ɵɵdefineInjectable({
  token: _PlatformLocation,
  factory: () => (() => inject(BrowserPlatformLocation))(),
  providedIn: "platform"
}));
var PlatformLocation = _PlatformLocation;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(PlatformLocation, [{
    type: Injectable,
    args: [{
      providedIn: "platform",
      useFactory: () => inject(BrowserPlatformLocation)
    }]
  }], null, null);
})();
var LOCATION_INITIALIZED = new InjectionToken(typeof ngDevMode !== "undefined" && ngDevMode ? "Location Initialized" : "");
var _BrowserPlatformLocation = class _BrowserPlatformLocation extends PlatformLocation {
  constructor() {
    super();
    __publicField(this, "_location");
    __publicField(this, "_history");
    __publicField(this, "_doc", inject(DOCUMENT));
    this._location = window.location;
    this._history = window.history;
  }
  getBaseHrefFromDOM() {
    return getDOM().getBaseHref(this._doc);
  }
  onPopState(fn) {
    const window2 = getDOM().getGlobalEventTarget(this._doc, "window");
    window2.addEventListener("popstate", fn, false);
    return () => window2.removeEventListener("popstate", fn);
  }
  onHashChange(fn) {
    const window2 = getDOM().getGlobalEventTarget(this._doc, "window");
    window2.addEventListener("hashchange", fn, false);
    return () => window2.removeEventListener("hashchange", fn);
  }
  get href() {
    return this._location.href;
  }
  get protocol() {
    return this._location.protocol;
  }
  get hostname() {
    return this._location.hostname;
  }
  get port() {
    return this._location.port;
  }
  get pathname() {
    return this._location.pathname;
  }
  get search() {
    return this._location.search;
  }
  get hash() {
    return this._location.hash;
  }
  set pathname(newPath) {
    this._location.pathname = newPath;
  }
  pushState(state, title, url) {
    this._history.pushState(state, title, url);
  }
  replaceState(state, title, url) {
    this._history.replaceState(state, title, url);
  }
  forward() {
    this._history.forward();
  }
  back() {
    this._history.back();
  }
  historyGo(relativePosition = 0) {
    this._history.go(relativePosition);
  }
  getState() {
    return this._history.state;
  }
};
__publicField(_BrowserPlatformLocation, "ɵfac", function BrowserPlatformLocation_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _BrowserPlatformLocation)();
});
__publicField(_BrowserPlatformLocation, "ɵprov", ɵɵdefineInjectable({
  token: _BrowserPlatformLocation,
  factory: () => (() => new _BrowserPlatformLocation())(),
  providedIn: "platform"
}));
var BrowserPlatformLocation = _BrowserPlatformLocation;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(BrowserPlatformLocation, [{
    type: Injectable,
    args: [{
      providedIn: "platform",
      useFactory: () => new BrowserPlatformLocation()
    }]
  }], () => [], null);
})();

export {
  parseCookieValue,
  XhrFactory,
  getDOM,
  setRootDomAdapter,
  DomAdapter,
  PlatformLocation,
  LOCATION_INITIALIZED,
  BrowserPlatformLocation
};
//# sourceMappingURL=chunk-BU4QRIRA.js.map
