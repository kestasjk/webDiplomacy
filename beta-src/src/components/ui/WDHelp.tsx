import React, {
  Fragment,
  ReactElement,
  FunctionComponent,
  useState,
} from "react";
import WDVerticalScroll from "./WDVerticalScroll";
import Move from "../../assets/help/move.gif";
import Hold from "../../assets/help/hold.gif";
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
    shortcutKey: "m",
  },
  {
    title: "Hold",
    image: Hold,
    description: "Select Unit or territory, select Hold.",
    shortcutKey: "h",
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
                  style={{ backgroundImage: `url(${Hold})` }}
                />
                <div className="flex mt-4 mb-2">
                  <div className="flex-1 text-lg font-bold">{item.title}</div>
                  <div className="flex">
                    <button
                      className="mr-1"
                      type="button"
                      onClick={() => {
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
                      onClick={() => {
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
                  {item.description} Shortcut Key:{" "}
                  <span className="font-bold">{item.shortcutKey}</span>
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
