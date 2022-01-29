import { Box } from "@mui/material";
import * as React from "react";
import PropTypes from "prop-types";
import Button from "@mui/material/Button";
import Avatar from "@mui/material/Avatar";
import List from "@mui/material/List";
import ListItem from "@mui/material/ListItem";
import ListItemAvatar from "@mui/material/ListItemAvatar";
import ListItemText from "@mui/material/ListItemText";
import DialogTitle from "@mui/material/DialogTitle";
import Dialog from "@mui/material/Dialog";
import PersonIcon from "@mui/icons-material/Person";
import AddIcon from "@mui/icons-material/Add";
import Typography from "@mui/material/Typography";
import { blue } from "@mui/material/colors";
import { types } from "util";

const emails = ["username@gmail.com", "user02@gmail.com"];

interface DialogProps {
  onClose: any;
  open: any;
  selectedValue: any;
  modalParentId: string;
}

const WDModal: React.FC<DialogProps> = function (props): React.ReactElement {
  const { onClose, selectedValue, open, modalParentId } = props;

  const handleClose = () => {
    onClose(selectedValue);
  };

  const handleListItemClick = (value) => {
    onClose(value);
  };

  return (
    <div id={modalParentId}>
      <Dialog
        onClose={handleClose}
        open={open}
        hideBackdrop
        container={() => {
          return document.getElementById(modalParentId);
        }}
      >
        <DialogTitle>Set backup account</DialogTitle>
        <List sx={{ pt: 0 }}>
          {emails.map((email) => (
            <ListItem
              button
              onClick={() => handleListItemClick(email)}
              key={email}
            >
              <ListItemAvatar>
                <Avatar sx={{ bgcolor: blue[100], color: blue[600] }}>
                  <PersonIcon />
                </Avatar>
              </ListItemAvatar>
              <ListItemText primary={email} />
            </ListItem>
          ))}

          <ListItem
            autoFocus
            button
            onClick={() => handleListItemClick("addAccount")}
          >
            <ListItemAvatar>
              <Avatar>
                <AddIcon />
              </Avatar>
            </ListItemAvatar>
            <ListItemText primary="Add account" />
          </ListItem>
        </List>
      </Dialog>
    </div>
  );
};

// const StyledModal = styled(WDModal)`
//   :after {
//     position: absolute;
//     content: "";
//     width: 0px;
//     height: 0px;
//     border-top: 20px solid transparent;
//     border-bottom: 20px solid transparent;
//     border-left: 20px solid grey;
//     top: 25%;
//     right: 10px;
//   }
// `;

interface SimpleDialogDemoProps {
  modalParentId: string;
}

const SimpleDialogDemo: React.FC<SimpleDialogDemoProps> = function (
  props,
): React.ReactElement {
  const { modalParentId } = props;
  const [open, setOpen] = React.useState(false);
  const [selectedValue, setSelectedValue] = React.useState(emails[1]);

  const handleClickOpen = () => {
    setOpen(true);
  };

  const handleClose = (value) => {
    setOpen(false);
    setSelectedValue(value);
  };

  return (
    <div>
      <div>
        <WDModal
          selectedValue={selectedValue}
          open={open}
          onClose={handleClose}
          modalParentId={modalParentId}
        />
        {/* <StyledModal
            selectedValue={selectedValue}
            open={open}
            onClose={handleClose}
            modalParentId={modalParentId}
          /> */}
        <Button variant="outlined" onClick={handleClickOpen}>
          Open simple dialog
        </Button>
      </div>
    </div>
  );
};

export default SimpleDialogDemo;
