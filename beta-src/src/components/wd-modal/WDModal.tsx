import * as React from "react";
import "./WDModal.css";
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
          sx={{
            mt: "15px",
          }}
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

        <div className="modal__content">{children}</div>
      </Popover>
    </div>
  );
};

export default WDModal;
