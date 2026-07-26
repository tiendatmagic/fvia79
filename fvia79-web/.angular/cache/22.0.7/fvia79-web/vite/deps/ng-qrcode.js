import {
  require_browser
} from "./chunk-3LSXSMJD.js";
import {
  CommonModule,
  NgStyle
} from "./chunk-P4IALK2B.js";
import "./chunk-BU4QRIRA.js";
import {
  ChangeDetectionStrategy,
  Component,
  Directive,
  Input,
  NgModule,
  ViewContainerRef,
  isDevMode,
  setClassMetadata,
  ɵɵNgOnChangesFeature,
  ɵɵclassMap,
  ɵɵconditional,
  ɵɵconditionalCreate,
  ɵɵdefineComponent,
  ɵɵdefineDirective,
  ɵɵdefineInjector,
  ɵɵdefineNgModule,
  ɵɵdirectiveInject,
  ɵɵelement,
  ɵɵnextContext,
  ɵɵproperty
} from "./chunk-JTAU332Q.js";
import "./chunk-HWYXSU2G.js";
import "./chunk-JRFR6BLO.js";
import "./chunk-MARUHEWW.js";
import {
  __publicField,
  __toESM
} from "./chunk-DPTKPNMV.js";

// node_modules/ng-qrcode/fesm2022/ng-qrcode.mjs
var import_qrcode = __toESM(require_browser(), 1);
function QrCodeComponent_Conditional_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelement(0, "canvas", 1);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext();
    ɵɵclassMap(ctx_r0.styleClass);
    ɵɵproperty("qrCode", ctx_r0.value)("qrCodeErrorCorrectionLevel", ctx_r0.errorCorrectionLevel)("qrCodeCenterImageSrc", ctx_r0.centerImageSrc)("qrCodeCenterImageWidth", ctx_r0.centerImageSize)("qrCodeCenterImageHeight", ctx_r0.centerImageSize)("qrCodeMargin", ctx_r0.margin)("qrScale", ctx_r0.scale)("qrCodeMaskPattern", ctx_r0.maskPattern)("width", ctx_r0.size)("height", ctx_r0.size)("ngStyle", ctx_r0.style)("darkColor", ctx_r0.darkColor)("lightColor", ctx_r0.lightColor);
  }
}
var validColorRegex = /^#(?:[0-9a-fA-F]{3,4}){1,2}$/;
var validNumberRegex = /^[0-9.]+$/;
var _QrCodeDirective = class _QrCodeDirective {
  constructor(viewContainerRef) {
    __publicField(this, "viewContainerRef");
    __publicField(this, "value");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "version");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "errorCorrectionLevel", _QrCodeDirective.DEFAULT_ERROR_CORRECTION_LEVEL);
    __publicField(this, "width");
    __publicField(this, "height");
    __publicField(this, "darkColor", "#000000FF");
    __publicField(this, "lightColor", "#FFFFFFFF");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "centerImageSrc");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "centerImageWidth");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "centerImageHeight");
    // eslint-disable-next-line @angular-eslint/no-input-rename
    __publicField(this, "margin", 16);
    __publicField(this, "qrScale");
    __publicField(this, "qrCodeMaskPattern");
    __publicField(this, "centerImage");
    this.viewContainerRef = viewContainerRef;
  }
  async ngOnChanges() {
    var _a;
    if (!this.value) {
      return;
    }
    if (this.version && this.version > 40) {
      console.warn("[qrCode] max version is 40, clamping");
      this.version = 40;
    } else if (this.version && this.version < 1) {
      console.warn("[qrCode] min version is 1, clamping");
      this.version = 1;
    } else if (this.version !== void 0 && isNaN(this.version)) {
      console.warn("[qrCode] version should be set to a number, defaulting to auto");
      this.version = void 0;
    }
    const canvas = this.viewContainerRef.element.nativeElement;
    if (!canvas) {
      return;
    }
    const context = canvas.getContext("2d");
    if (context) {
      context.clearRect(0, 0, context.canvas.width, context.canvas.height);
    }
    const errorCorrectionLevel = (_a = this.errorCorrectionLevel) != null ? _a : _QrCodeDirective.DEFAULT_ERROR_CORRECTION_LEVEL;
    const dark = this.darkColor && validColorRegex.test(this.darkColor) ? this.darkColor : void 0;
    const light = this.lightColor && validColorRegex.test(this.lightColor) ? this.lightColor : void 0;
    if (isDevMode()) {
      if (!dark && this.darkColor) {
        console.error("[ng-qrcode] darkColor set to invalid value, must be RGBA hex color string, eg: #3050A1FF");
      }
      if (!light && this.lightColor) {
        console.error("[ng-qrcode] lightColor set to invalid value, must be RGBA hex color string, eg: #3050A130");
      }
    }
    await import_qrcode.default.toCanvas(canvas, this.value, {
      version: this.version,
      errorCorrectionLevel,
      width: getOptionalInt(this.width),
      margin: getOptionalInt(this.margin),
      scale: getOptionalInt(this.qrScale),
      maskPattern: this.qrCodeMaskPattern,
      color: {
        dark,
        light
      }
    });
    const centerImageSrc = this.centerImageSrc;
    const centerImageWidth = getIntOrDefault(this.centerImageWidth, _QrCodeDirective.DEFAULT_CENTER_IMAGE_SIZE);
    const centerImageHeight = getIntOrDefault(this.centerImageHeight, _QrCodeDirective.DEFAULT_CENTER_IMAGE_SIZE);
    if (centerImageSrc && context) {
      if (!this.centerImage) {
        this.centerImage = new Image(centerImageWidth, centerImageHeight);
      }
      const centerImage = this.centerImage;
      if (centerImageSrc !== this.centerImage.src) {
        centerImage.src = centerImageSrc;
      }
      if (centerImageWidth !== this.centerImage.width) {
        centerImage.width = centerImageWidth;
      }
      if (centerImageHeight !== this.centerImage.height) {
        centerImage.height = centerImageHeight;
      }
      const doDrawImage = () => {
        context.drawImage(centerImage, canvas.width / 2 - centerImageWidth / 2, canvas.height / 2 - centerImageHeight / 2, centerImageWidth, centerImageHeight);
      };
      centerImage.onload = doDrawImage;
      if (centerImage.complete) {
        doDrawImage();
      }
    }
  }
};
__publicField(_QrCodeDirective, "DEFAULT_ERROR_CORRECTION_LEVEL", "M");
__publicField(_QrCodeDirective, "DEFAULT_CENTER_IMAGE_SIZE", 40);
__publicField(_QrCodeDirective, "ɵfac", function QrCodeDirective_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _QrCodeDirective)(ɵɵdirectiveInject(ViewContainerRef));
});
__publicField(_QrCodeDirective, "ɵdir", ɵɵdefineDirective({
  type: _QrCodeDirective,
  selectors: [["canvas", "qrCode", ""]],
  inputs: {
    value: [0, "qrCode", "value"],
    version: [0, "qrCodeVersion", "version"],
    errorCorrectionLevel: [0, "qrCodeErrorCorrectionLevel", "errorCorrectionLevel"],
    width: "width",
    height: "height",
    darkColor: "darkColor",
    lightColor: "lightColor",
    centerImageSrc: [0, "qrCodeCenterImageSrc", "centerImageSrc"],
    centerImageWidth: [0, "qrCodeCenterImageWidth", "centerImageWidth"],
    centerImageHeight: [0, "qrCodeCenterImageHeight", "centerImageHeight"],
    margin: [0, "qrCodeMargin", "margin"],
    qrScale: "qrScale",
    qrCodeMaskPattern: "qrCodeMaskPattern"
  },
  features: [ɵɵNgOnChangesFeature]
}));
var QrCodeDirective = _QrCodeDirective;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(QrCodeDirective, [{
    type: Directive,
    args: [{
      // eslint-disable-next-line @angular-eslint/directive-selector
      selector: `canvas[qrCode]`,
      standalone: true
    }]
  }], () => [{
    type: ViewContainerRef
  }], {
    value: [{
      type: Input,
      args: ["qrCode"]
    }],
    version: [{
      type: Input,
      args: ["qrCodeVersion"]
    }],
    errorCorrectionLevel: [{
      type: Input,
      args: ["qrCodeErrorCorrectionLevel"]
    }],
    width: [{
      type: Input
    }],
    height: [{
      type: Input
    }],
    darkColor: [{
      type: Input
    }],
    lightColor: [{
      type: Input
    }],
    centerImageSrc: [{
      type: Input,
      args: ["qrCodeCenterImageSrc"]
    }],
    centerImageWidth: [{
      type: Input,
      args: ["qrCodeCenterImageWidth"]
    }],
    centerImageHeight: [{
      type: Input,
      args: ["qrCodeCenterImageHeight"]
    }],
    margin: [{
      type: Input,
      args: ["qrCodeMargin"]
    }],
    qrScale: [{
      type: Input
    }],
    qrCodeMaskPattern: [{
      type: Input
    }]
  });
})();
function getOptionalInt(value) {
  if (value === void 0 || value === "") {
    return void 0;
  }
  if (typeof value === "string") {
    if (!validNumberRegex.test(value)) {
      throw new Error(`'${value}' is not a valid number`);
    }
    return parseFloat(value);
  }
  return value;
}
function getIntOrDefault(value, defaultValue) {
  if (value === void 0 || value === "") {
    return defaultValue;
  }
  return getOptionalInt(value);
}
var _QrCodeComponent = class _QrCodeComponent {
  constructor() {
    __publicField(this, "value");
    __publicField(this, "size");
    __publicField(this, "style");
    __publicField(this, "styleClass");
    __publicField(this, "darkColor");
    __publicField(this, "lightColor");
    __publicField(this, "errorCorrectionLevel");
    __publicField(this, "centerImageSrc");
    __publicField(this, "centerImageSize");
    __publicField(this, "margin");
    __publicField(this, "scale");
    __publicField(this, "maskPattern");
  }
};
__publicField(_QrCodeComponent, "ɵfac", function QrCodeComponent_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _QrCodeComponent)();
});
__publicField(_QrCodeComponent, "ɵcmp", ɵɵdefineComponent({
  type: _QrCodeComponent,
  selectors: [["qr-code"]],
  inputs: {
    value: "value",
    size: "size",
    style: "style",
    styleClass: "styleClass",
    darkColor: "darkColor",
    lightColor: "lightColor",
    errorCorrectionLevel: "errorCorrectionLevel",
    centerImageSrc: "centerImageSrc",
    centerImageSize: "centerImageSize",
    margin: "margin",
    scale: "scale",
    maskPattern: "maskPattern"
  },
  decls: 1,
  vars: 1,
  consts: [[3, "qrCode", "qrCodeErrorCorrectionLevel", "qrCodeCenterImageSrc", "qrCodeCenterImageWidth", "qrCodeCenterImageHeight", "qrCodeMargin", "qrScale", "qrCodeMaskPattern", "width", "height", "class", "ngStyle", "darkColor", "lightColor"], [3, "qrCode", "qrCodeErrorCorrectionLevel", "qrCodeCenterImageSrc", "qrCodeCenterImageWidth", "qrCodeCenterImageHeight", "qrCodeMargin", "qrScale", "qrCodeMaskPattern", "width", "height", "ngStyle", "darkColor", "lightColor"]],
  template: function QrCodeComponent_Template(rf, ctx) {
    if (rf & 1) {
      ɵɵconditionalCreate(0, QrCodeComponent_Conditional_0_Template, 1, 15, "canvas", 0);
    }
    if (rf & 2) {
      ɵɵconditional(ctx.value ? 0 : -1);
    }
  },
  dependencies: [QrCodeDirective, CommonModule, NgStyle],
  encapsulation: 2
}));
var QrCodeComponent = _QrCodeComponent;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(QrCodeComponent, [{
    type: Component,
    args: [{
      selector: "qr-code",
      template: `
    @if (value) {
      <canvas
        [qrCode]="value"
        [qrCodeErrorCorrectionLevel]="errorCorrectionLevel"
        [qrCodeCenterImageSrc]="centerImageSrc"
        [qrCodeCenterImageWidth]="centerImageSize"
        [qrCodeCenterImageHeight]="centerImageSize"
        [qrCodeMargin]="margin"
        [qrScale]="scale"
        [qrCodeMaskPattern]="maskPattern"
        [width]="size"
        [height]="size"
        [class]="styleClass"
        [ngStyle]="style"
        [darkColor]="darkColor"
        [lightColor]="lightColor"
      >
      </canvas>
    }
  `,
      standalone: true,
      changeDetection: ChangeDetectionStrategy.OnPush,
      imports: [QrCodeDirective, CommonModule]
    }]
  }], null, {
    value: [{
      type: Input
    }],
    size: [{
      type: Input
    }],
    style: [{
      type: Input
    }],
    styleClass: [{
      type: Input
    }],
    darkColor: [{
      type: Input
    }],
    lightColor: [{
      type: Input
    }],
    errorCorrectionLevel: [{
      type: Input
    }],
    centerImageSrc: [{
      type: Input
    }],
    centerImageSize: [{
      type: Input
    }],
    margin: [{
      type: Input
    }],
    scale: [{
      type: Input
    }],
    maskPattern: [{
      type: Input
    }]
  });
})();
var _QrCodeModule = class _QrCodeModule {
};
__publicField(_QrCodeModule, "ɵfac", function QrCodeModule_Factory(__ngFactoryType__) {
  return new (__ngFactoryType__ || _QrCodeModule)();
});
__publicField(_QrCodeModule, "ɵmod", ɵɵdefineNgModule({
  type: _QrCodeModule,
  imports: [CommonModule, QrCodeComponent, QrCodeDirective],
  exports: [QrCodeComponent, QrCodeDirective]
}));
__publicField(_QrCodeModule, "ɵinj", ɵɵdefineInjector({
  imports: [CommonModule, QrCodeComponent]
}));
var QrCodeModule = _QrCodeModule;
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(QrCodeModule, [{
    type: NgModule,
    args: [{
      declarations: [],
      imports: [CommonModule, QrCodeComponent, QrCodeDirective],
      exports: [QrCodeComponent, QrCodeDirective]
    }]
  }], null, null);
})();
export {
  QrCodeComponent,
  QrCodeDirective,
  QrCodeModule,
  getIntOrDefault,
  getOptionalInt
};
//# sourceMappingURL=ng-qrcode.js.map
