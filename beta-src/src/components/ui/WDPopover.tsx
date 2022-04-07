import * as React from "react";
import {
  Box,
  ModalProps,
  Popover,
  useMediaQuery,
  useTheme,
} from "@mui/material";
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
  anchorEl: HTMLElement;
}

const WDPopover: React.FC<WDPopoverProps> = function ({
  children,
  isOpen,
  onClose,
  anchorEl,
}): React.ReactElement {
  const theme = useTheme();
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  let isMobile: boolean;
  switch (device) {
    case Device.MOBILE:
    case Device.MOBILE_LG:
    case Device.MOBILE_LANDSCAPE:
    case Device.MOBILE_LG_LANDSCAPE:
      isMobile = true;
      break;
    default:
      isMobile = false;
      break;
  }
  return (
    <Box>
      <Popover
        anchorEl={anchorEl}
        anchorOrigin={{
          horizontal: "left",
          vertical: "center",
        }}
        onClose={onClose}
        open={isOpen}
        sx={{ pointerEvents: "none" }}
        PaperProps={{
          sx: {
            backgroundColor: "transparent",
            boxShadow: "none",
            maxHeight: "unset",
            pointerEvents: "all",
            filter: theme.palette.svg.filters.dropShadows[0],
          },
        }}
        transformOrigin={{
          horizontal: "right",
          vertical: "center",
        }}
      >
        <Box
          sx={{
            position: "relative",
            width: isMobile ? 276 : 400,
          }}
        >
          <Box
            sx={{
              mt: "0px",
              position: "relative",
              "&::before": {
                background: theme.palette.secondary.main,
                content: '""',
                height: 22,
                position: "absolute",
                left: "calc(90% - 11px)",
                top: useMediaQuery(theme.breakpoints.up("tablet")) ? 76 : 68,
                transform: "rotateX(45deg) rotateZ(45deg)",
                width: 22,
              },
            }}
          />
          <Box
            sx={{
              background: theme.palette.secondary.main,
              borderRadius: 3,
              maxWidth: "90%",
              minHeight: 264,
              maxHeight: viewport.height - 32,
              overflowX: "hidden",
              overflowY: "scroll",
              m: 0,
              p: "16px 0",
            }}
          >
            {children}
          </Box>
        </Box>
      </Popover>
    </Box>
  );
};

WDPopover.defaultProps = {
  onClose: undefined,
};

export default WDPopover;
