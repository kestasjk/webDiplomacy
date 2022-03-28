import * as React from "react";
import { useRef } from "react";
import { Box, ModalProps, Popover } from "@mui/material";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import Device from "../../enums/Device";

interface WDPopoverProps {
  children: React.ReactNode;
  isOpen: boolean;
  /**
   * A optional callback to be called when the Popover closes. For example:
   *
   * () => setIsOpen(false)
   */
  onClose?: ModalProps["onClose"];
  /**
   * A component that opens or closes the Popover when clicked.
   */
  popoverTrigger: React.ReactNode;
}

const WDPopover: React.FC<WDPopoverProps> = function ({
  children,
  isOpen,
  onClose,
  popoverTrigger,
}) {
  const anchorEl = useRef(null);
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const top = mobileLandscapeLayout ? 32 : 30;

  return (
    <Box>
      <Box
        sx={{
          pt: "15px",
        }}
        ref={anchorEl}
      >
        {popoverTrigger}
      </Box>
      <Popover
        anchorEl={anchorEl.current}
        anchorOrigin={{
          horizontal: "left",
          vertical: "center",
        }}
        onClose={onClose}
        open={isOpen}
        PaperProps={{
          style: {
            backgroundColor: "transparent",
            borderRadius: "3px 13px 13px 3px",
            boxShadow: "-5px 4px 6px -4px black",
          },
        }}
        transformOrigin={{
          horizontal: "right",
          vertical: "center",
        }}
      >
        <Box
          sx={{
            mt: "0px",
            position: "relative",
            "&::before": {
              background: "linear-gradient(45deg, transparent 50%, white 50%)",
              content: '""',
              height: 22,
              position: "absolute",
              right: 3,
              top,
              transform: "rotate(45deg)",
              width: 22,
            },
          }}
        />

        <Box
          sx={{
            background: "linear-gradient(to right, white 97%, transparent 3%)",
            m: 0,
            p: "16px 9px 16px 0",
          }}
        >
          {children}
        </Box>
      </Popover>
    </Box>
  );
};

WDPopover.defaultProps = {
  onClose: undefined,
};

export default WDPopover;
