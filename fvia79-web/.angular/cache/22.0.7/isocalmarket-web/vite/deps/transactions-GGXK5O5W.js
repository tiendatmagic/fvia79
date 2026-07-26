import "./chunk-TO7LYVBW.js";
import "./chunk-XFSCI6HL.js";
import "./chunk-4YSJ3LMJ.js";
import "./chunk-IMY2X5LP.js";
import "./chunk-CGXHVTOC.js";
import "./chunk-EFVTHT6X.js";
import "./chunk-KMEWIWOW.js";
import {
  LitElement,
  css,
  customElement,
  html
} from "./chunk-QS525LBY.js";
import "./chunk-44K7Z3II.js";
import "./chunk-MLMBTJ4I.js";
import "./chunk-YIA57MLI.js";
import "./chunk-MDP4QN57.js";
import "./chunk-HM2REMIQ.js";
import "./chunk-5VPONNAA.js";
import "./chunk-AEFHGNSP.js";
import "./chunk-PQKOZCNK.js";
import "./chunk-JVTPAVRS.js";
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
//# sourceMappingURL=transactions-GGXK5O5W.js.map
