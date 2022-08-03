import * as React from "react";
import { useWindowSize } from "react-use";

interface WDPopoverProps {
  children: React.ReactNode;
  isOpen: boolean;
}

const WDPopover: React.FC<WDPopoverProps> = function ({
  children,
  isOpen,
}): React.ReactElement {
  const { width, height } = useWindowSize();

  return (
    // eslint-disable-next-line react/jsx-no-useless-fragment
    <>
      {isOpen && (
        <div
          className="fixed"
          style={{
            bottom: width < 500 ? 0 : 70,
            left: width < 500 ? 0 : "unset",
            right: width > 500 ? 100 : "unset",
          }}
        >
          <div>
            <div
              className="relative"
              style={{
                width: width < 500 ? width : 400,
              }}
            >
              <div
                className="bg-white m-0 sm:rounded-lg overflow-x-hidden overflow-y-scroll py-4"
                style={{
                  minHeight: 264,
                  maxHeight: height - 32,
                }}
              >
                {children}
                {width > 500 && (
                  <div className="absolute bottom-20 right-[-12px] w-6 h-6 bg-white rotate-45" />
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

WDPopover.defaultProps = {};

export default WDPopover;
