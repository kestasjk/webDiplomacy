import * as React from "react";
import { useRef } from "react";
import { Popover, Box } from "@mui/material";

interface WDPopoverProps {
  children: React.ReactNode;
  handleClose: (event: React.MouseEvent<HTMLButtonElement>) => void; // A function that sets isOpen to false
  isOpen: boolean;
  trigger: React.ReactNode; // The popover trigger; must have an onClick callback attached that sets isOpen to true
}

const WDPopover: React.FC<WDPopoverProps> = function ({
  children,
  handleClose,
  isOpen,
  trigger,
}) {
  const anchorEl = useRef(null);

  return (
    <Box
      sx={{
        position: "absolute",
      }}
    >
      <Box
        sx={{
          pt: "15px",
        }}
        ref={anchorEl}
      >
        {trigger}
      </Box>
      <Popover
        open={isOpen}
        anchorEl={anchorEl.current}
        onClose={handleClose}
        anchorOrigin={{
          vertical: "center",
          horizontal: "left",
        }}
        transformOrigin={{
          vertical: "center",
          horizontal: "right",
        }}
        PaperProps={{
          style: {
            backgroundColor: "transparent",
            boxShadow: "black -5px 4px 6px -4px",
            borderRadius: "3px 13px 13px 3px",
          },
        }}
      >
        <Box
          sx={{
            mt: "0px",
            position: "relative",
            "&::before": {
              background: "linear-gradient(45deg, transparent 50%, white 50%)",
              content: '""',
              display: "block",
              height: 22,
              position: "absolute",
              right: 5,
              top: 10,
              transform: "rotate(45deg)",
              width: 22,
            },
          }}
        />

        <Box
          sx={{
            background: "linear-gradient(to right, white 94%, transparent 6%)",
            maxWidth: "57vw",
            m: 0,
            p: "16px 25px 16px 16px",
          }}
        >
          {children}
        </Box>
      </Popover>
    </Box>
  );
};

export default WDPopover;
