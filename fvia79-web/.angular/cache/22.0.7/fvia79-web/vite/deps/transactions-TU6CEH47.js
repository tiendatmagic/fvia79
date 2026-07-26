import "./chunk-H7SDPSMQ.js";
import "./chunk-IIKENDJC.js";
import "./chunk-2KQEVJWV.js";
import "./chunk-CGXHVTOC.js";
import "./chunk-WNUNVEGU.js";
import "./chunk-QBPMHSXI.js";
import "./chunk-B467Y3ZZ.js";
import {
  LitElement,
  css,
  customElement,
  html
} from "./chunk-DW4L56D2.js";
import "./chunk-U4RVBNBZ.js";
import "./chunk-NWB6EVMS.js";
import "./chunk-HBKANTW7.js";
import "./chunk-JVTPAVRS.js";
import "./chunk-MDP4QN57.js";
import "./chunk-6ZPCXYG2.js";
import "./chunk-5FGWSVQB.js";
import "./chunk-I43WISOI.js";
import "./chunk-PQKOZCNK.js";
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
//# sourceMappingURL=transactions-TU6CEH47.js.map
