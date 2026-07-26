import "./chunk-EPQYLLOD.js";
import "./chunk-VMHGZTYX.js";
import "./chunk-H3N6I32R.js";
import "./chunk-B362GR2C.js";
import "./chunk-CGXHVTOC.js";
import "./chunk-VKOPETSF.js";
import "./chunk-H6KHTRZE.js";
import {
  LitElement,
  css,
  customElement,
  html
} from "./chunk-K7LFVOJG.js";
import "./chunk-MZWFCEKP.js";
import "./chunk-47XO7YVI.js";
import "./chunk-ZUZ5BVRF.js";
import "./chunk-JVTPAVRS.js";
import "./chunk-MDP4QN57.js";
import "./chunk-Z24IRVSI.js";
import "./chunk-TN5QMT3D.js";
import "./chunk-NQNRHY57.js";
import "./chunk-LV4EC3K4.js";
import "./chunk-DPTKPNMV.js";

// node_modules/@reown/appkit-scaffold-ui/dist/esm/src/views/w3m-transactions-view/styles.js
var styles_default = css`
  :host > wui-flex:first-child {
    height: 500px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: none;
  }

  :host > wui-flex:first-child::-webkit-scrollbar {
    display: none;
  }
`;

// node_modules/@reown/appkit-scaffold-ui/dist/esm/src/views/w3m-transactions-view/index.js
var __decorate = function(decorators, target, key, desc) {
  var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
  if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
  else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
  return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var W3mTransactionsView = class W3mTransactionsView2 extends LitElement {
  render() {
    return html`
      <wui-flex flexDirection="column" .padding=${["0", "3", "3", "3"]} gap="3">
        <w3m-activity-list page="activity"></w3m-activity-list>
      </wui-flex>
    `;
  }
};
W3mTransactionsView.styles = styles_default;
W3mTransactionsView = __decorate([
  customElement("w3m-transactions-view")
], W3mTransactionsView);
export {
  W3mTransactionsView
};
//# sourceMappingURL=transactions-R2FIE7XO.js.map
