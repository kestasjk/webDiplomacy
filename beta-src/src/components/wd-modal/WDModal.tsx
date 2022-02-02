import * as React from "react";
import { useRef, useState } from "react";
import { Popover, Button, Box } from "@mui/material";

interface WDModalProps {
  ariaDescribedBy: string;
  children: React.ReactNode;
  triggerIcon: React.ReactNode;
}

const WDModal: React.FC<WDModalProps> = function ({
  ariaDescribedBy,
  children,
  triggerIcon,
}) {
  const anchorEl = useRef(null);

  const [isOpen, setIsOpen] = useState(false);

  const handleOpen = () => {
    setIsOpen(true);
  };

  const handleClose = () => {
    setIsOpen(false);
  };

  return (
    <div className="modal__container">
      <div className="modal__trigger-container">
        <Button
          aria-describedby={ariaDescribedBy}
          onClick={handleOpen}
          ref={anchorEl}
        >
          {triggerIcon}
        </Button>
      </div>
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
            boxShadow: "none",
            borderRadius: 0,
          },
        }}
      >
        <Box
          sx={{
            position: "relative",
            mt: "0px",
            "&::before": {
              background: "linear-gradient(45deg, transparent 50%, white 50%)",
              content: '""',
              display: "block",
              position: "absolute",
              width: 22,
              height: 22,
              top: 10,
              transform: "rotate(45deg)",
              right: 5,
            },
          }}
        />

        <div className="modal__content">{children}</div>
      </Popover>
    </div>
  );
};

export default WDModal;
