import { Box } from "@mui/material";
import * as React from "react";
import Popover from "@mui/material/Popover";
import Typography from "@mui/material/Typography";
import Button from "@mui/material/Button";

const WDModal: React.FC = function () {
  const [anchorEl, setAnchorEl] = React.useState(null);

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const open = Boolean(anchorEl);
  const accessibilityTag = open ? "simple-popover" : undefined;

  return (
    <div className="modal__container">
      <Button
        aria-describedby={accessibilityTag}
        variant="contained"
        onClick={handleClick}
      >
        BUTTON STUB
      </Button>
      <Popover
        open={open}
        anchorEl={anchorEl}
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
        sx={{
          top: 0,
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
        <Typography
          sx={{
            p: 2,
            background: "linear-gradient(to right, white 96%, transparent 4%)",
            maxWidth: "57vw",
          }}
        >
          <div className="modal__content">
            Are you sure you want to exit the game?
          </div>
        </Typography>
      </Popover>
    </div>
  );
};

export default WDModal;
