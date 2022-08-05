import React, {
  Fragment,
  ReactElement,
  FunctionComponent,
  useState,
  MouseEvent,
} from "react";
import WDVerticalScroll from "./WDVerticalScroll";
import Move from "../../assets/help/move.gif";
import Convoy from "../../assets/help/convoy.gif";
import Hold from "../../assets/help/hold.gif";
import SupportHold from "../../assets/help/support-hold.gif";
import SupportMove from "../../assets/help/support-move.gif";
import Retreat from "../../assets/help/retreat.gif";
import Build from "../../assets/help/build.gif";
import ViaConvoy from "../../assets/help/via-convoy.gif";

import { ReactComponent as BtnArrowIcon } from "../../assets/svg/btnArrow.svg";

interface itemProps {
  title: string;
  image: any;
  description: string;
  shortcutKey: string;
}

const items: itemProps[] = [
  {
    title: "Move",
    image: Move,
    description:
      "Select Unit (or territory), select Move, select Destination Territory.",
    shortcutKey: "m or a",
  },
  {
    title: "Convoy",
    image: Convoy,
    description:
      "Select Army (or territory), select Convoy, select Destination Territory.",
    shortcutKey: "c",
  },
  {
    title: "Via Convoy",
    image: ViaConvoy,
    description:
      "Select Army (or territory), select Via Convoy, select Destination Territory.",
    shortcutKey: "v",
  },
  {
    title: "Hold",
    image: Hold,
    description: "Select Unit or territory, select Hold.",
    shortcutKey: "h or d",
  },
  {
    title: "Support Hold",
    image: SupportHold,
    description:
      "Select Supporting Unit (or territory), select Support, select Unit (or territory) to Support.",
    shortcutKey: "",
  },
  {
    title: "Support Move",
    image: SupportMove,
    description:
      "Select Supporting Unit (or territory), select Support, select Unit (or territory) to Support, select Destination Territory.",
    shortcutKey: "s",
  },
  {
    title: "Retreat",
    image: Retreat,
    description:
      "Select Unit (or territory), select Retreat, select Destination Territory.",
    shortcutKey: "",
  },
  {
    title: "Build",
    image: Build,
    description:
      "Select supply center territory, select type of unit (Army or Fleet).",
    shortcutKey: "",
  },
];

const WDHelp: FunctionComponent = function (): ReactElement {
  const [currentIndex, setCurrentIndex] = useState<number>(0);

  return (
    <WDVerticalScroll>
      <div className="mt-3 px-3 sm:px-4">
        {items.map((item: itemProps, index: number) => (
          <Fragment key={item.title}>
            {currentIndex === index && (
              <>
                <div
                  className="w-full h-48 bg-cover bg-center rounded-2xl"
                  style={{ backgroundImage: `url(${item.image})` }}
                />
                <div className="flex mt-4 mb-2">
                  <div className="flex-1 text-lg font-bold">{item.title}</div>
                  <div className="flex">
                    <button
                      className="mr-1"
                      type="button"
                      onClick={(event: MouseEvent<HTMLButtonElement>) => {
                        event.stopPropagation();
                        if (index > 0) {
                          setCurrentIndex(index - 1);
                        }
                      }}
                    >
                      <BtnArrowIcon
                        className={`text-black stroke-white ${
                          index === 0 && "text-gray-300 cursor-not-allowed"
                        }`}
                      />
                    </button>
                    <button
                      type="button"
                      onClick={(event: MouseEvent<HTMLButtonElement>) => {
                        event.stopPropagation();
                        if (index < items.length - 1) {
                          setCurrentIndex(index + 1);
                        }
                      }}
                    >
                      <BtnArrowIcon
                        className={`scale-y-[-1] text-black stroke-white ${
                          index === items.length - 1 &&
                          "text-gray-300 cursor-not-allowed"
                        }`}
                      />
                    </button>
                  </div>
                </div>
                <div>
                  {item.description}{" "}
                  {item.shortcutKey && (
                    <span>
                      Shortcut Key:{" "}
                      <span className="font-bold">{item.shortcutKey}</span>
                    </span>
                  )}
                </div>
              </>
            )}
          </Fragment>
        ))}
      </div>
    </WDVerticalScroll>
  );
};

export default WDHelp;
