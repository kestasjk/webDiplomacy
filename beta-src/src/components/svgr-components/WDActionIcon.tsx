import * as React from "react";
import { navIconProps } from "../../interfaces/Icons";
import UIState from "../../enums/UIState";

const WDActionIcon: React.FC<navIconProps> = function ({
  iconState = UIState.INACTIVE,
}): React.ReactElement {
  return (
    <svg
      filter="drop-shadow(-1px 12px 7px #636363)"
      height={42}
      width={42}
      viewBox="6 3 42 42"
      xmlns="http://www.w3.org/2000/svg"
    >
      {iconState === UIState.ACTIVE && (
        <circle cx={27} cy={24} r={20} fill="#000" />
      )}
      <g
        filter={
          iconState === UIState.ACTIVE
            ? "drop-shadow(.5px 2px 6px #404040)"
            : ""
        }
        fill="#fff"
      >
        <path d="M37.12 20.147h-4.293c-.88 0-1.651.77-1.651 1.651v4.294c0 .88.77 1.651 1.651 1.651h4.294c.88 0 1.651-.77 1.651-1.651v-4.294c0-.88-.77-1.651-1.651-1.651Zm-.22 4.073-1.21 1.211c-.11.11-.11.11-.22.11s-.22 0-.22-.11a.335.335 0 0 1 0-.44l.66-.66h-2.642c-.22 0-.33-.11-.33-.331 0-.22.11-.33.33-.33H35.8l-.55-.66a.335.335 0 0 1 0-.441c.11-.11.33-.11.44 0l1.21 1.21c.11.11.11.221 0 .441ZM20.718 20.147h-4.183c-.881 0-1.652.77-1.652 1.651v4.294c0 .88.77 1.651 1.652 1.651h4.293c.88 0 1.652-.77 1.652-1.651v-4.294c-.11-.88-.771-1.651-1.762-1.651Zm-.33 4.073h-2.642l.66.66c.11.11.11.331 0 .441-.11.11-.11.11-.22.11s-.22 0-.22-.11l-1.211-1.21a.335.335 0 0 1 0-.441l1.21-1.211c.11-.11.331-.11.441 0 .11.11.11.33 0 .44l-.66.66h2.642c.22 0 .33.11.33.33 0 .221-.22.331-.33.331ZM28.975 28.404h-4.293c-.88 0-1.651.77-1.651 1.651v4.294c0 .88.77 1.651 1.65 1.651h4.294c.881 0 1.652-.77 1.652-1.651v-4.294c0-.99-.77-1.651-1.652-1.651Zm-.66 4.403-1.211 1.211c-.11.11-.33.11-.44 0l-1.212-1.21c-.11-.11-.11-.11-.11-.22 0-.111 0-.221.11-.221.11-.11.33-.11.44 0l.661.66v-2.642c0-.22.11-.33.33-.33.22 0 .331.11.331.33v2.532l.66-.66c.11-.11.33-.11.44 0 .111.22.111.44 0 .55ZM28.975 12h-4.293c-.88 0-1.651.77-1.651 1.651v4.294c0 .88.77 1.651 1.65 1.651h4.294c.881 0 1.652-.77 1.652-1.651V13.65c0-.88-.77-1.651-1.652-1.651Zm-.66 3.523c-.11.11-.33.11-.44 0l-.661-.66v2.532c0 .22-.11.33-.33.33-.22 0-.33-.11-.33-.33v-2.533l-.661.66c-.11.111-.33.111-.44 0a.335.335 0 0 1 0-.44l1.21-1.21c.11-.11.33-.11.44 0l1.212 1.21c.11.11.11.11.11.22s-.11.22-.11.22Z" />
      </g>
      <defs>
        <filter
          colorInterpolationFilters="sRGB"
          filterUnits="userSpaceOnUse"
          height={52}
          id="actionIcon-selected_svg__a"
          width={51.889}
          x={0.883}
          y={0}
        >
          <feFlood floodOpacity={0} result="BackgroundImageFix" />
          <feColorMatrix
            in="SourceAlpha"
            result="hardAlpha"
            values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
          />
          <feComposite in2="hardAlpha" operator="out" />
          <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" />
        </filter>
      </defs>
    </svg>
  );
};

export default WDActionIcon;
