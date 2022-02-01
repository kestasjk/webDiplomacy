// import { Box } from "@mui/material";
// import * as React from "react";
// import PropTypes from "prop-types";
// import Button from "@mui/material/Button";
// import Avatar from "@mui/material/Avatar";
// import List from "@mui/material/List";
// import ListItem from "@mui/material/ListItem";
// import ListItemAvatar from "@mui/material/ListItemAvatar";
// import ListItemText from "@mui/material/ListItemText";
// import DialogTitle from "@mui/material/DialogTitle";
// import Dialog from "@mui/material/Dialog";
// import PersonIcon from "@mui/icons-material/Person";
// import AddIcon from "@mui/icons-material/Add";
// import Typography from "@mui/material/Typography";
// import { blue } from "@mui/material/colors";
// import Tooltip from "@mui/material/Tooltip";
// import { types } from "util";

// const emails = ["username@gmail.com", "user02@gmail.com"];

// interface DialogProps {
//   onClose: any;
//   open: any;
//   selectedValue: any;
//   modalParentId: string;
//   topValue: string;
// }

// const WDModal: React.FC<DialogProps> = function (props): React.ReactElement {
//   const { onClose, selectedValue, open, modalParentId, topValue } = props;

//   React.useEffect(() => {
//     const modalParent = document.getElementById(modalParentId);
//     const modalElement = modalParent?.querySelectorAll(
//       ".css-10wsz8c-MuiPaper-root-MuiDialog-paper",
//     );
//     console.log(modalElement);
//     // if (modalElement) {
//     //   modalElement.style.top = topValue;
//     // }
//   });

//   const handleClose = () => {
//     onClose(selectedValue);
//   };

//   const handleListItemClick = (value) => {
//     onClose(value);
//   };

//   return (
//     <Dialog
//       onClose={handleClose}
//       open={open}
//       hideBackdrop
//       container={() => {
//         return document.getElementById(modalParentId);
//       }}
//     >
//       <DialogTitle>Set backup account</DialogTitle>
//       <List sx={{ pt: 0 }}>
//         {emails.map((email) => (
//           <ListItem
//             button
//             onClick={() => handleListItemClick(email)}
//             key={email}
//           >
//             <ListItemAvatar>
//               <Avatar sx={{ bgcolor: blue[100], color: blue[600] }}>
//                 <PersonIcon />
//               </Avatar>
//             </ListItemAvatar>
//             <ListItemText primary={email} />
//           </ListItem>
//         ))}

//         <ListItem
//           autoFocus
//           button
//           onClick={() => handleListItemClick("addAccount")}
//         >
//           <ListItemAvatar>
//             <Avatar>
//               <AddIcon />
//             </Avatar>
//           </ListItemAvatar>
//           <ListItemText primary="Add account" />
//         </ListItem>
//       </List>
//     </Dialog>
//   );
// };

// interface SimpleDialogDemoProps {
//   modalParentId: string;
//   topValue: string;
// }

// const SimpleDialogDemo: React.FC<SimpleDialogDemoProps> = function (
//   props,
// ): React.ReactElement {
//   const { modalParentId, topValue } = props;
//   const [open, setOpen] = React.useState(false);
//   const [selectedValue, setSelectedValue] = React.useState(emails[1]);

//   const handleClickOpen = () => {
//     setOpen(true);
//   };

//   const handleClose = (value) => {
//     setOpen(false);
//     setSelectedValue(value);
//   };

//   return (
//     <div
//       id={modalParentId}
//       style={{ top: topValue }}
//       className="dialog__container"
//     >
//       <WDModal
//         selectedValue={selectedValue}
//         open={open}
//         onClose={handleClose}
//         modalParentId={modalParentId}
//         topValue={topValue}
//       />
//       <Button variant="outlined" onClick={handleClickOpen}>
//         Open simple dialog
//       </Button>
//     </div>
//   );
// };

// export default SimpleDialogDemo;

import * as React from "react";
import Popover from "@mui/material/Popover";
import Typography from "@mui/material/Typography";
import Button from "@mui/material/Button";

interface SimpleDialogDemoProps {
  modalParentId: string;
  topValue: string;
}

const SimpleDialogDemo: React.FC<SimpleDialogDemoProps> =
  function BasicPopover() {
    const [anchorEl, setAnchorEl] = React.useState(null);

    const handleClick = (event) => {
      setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
      setAnchorEl(null);
    };

    const open = Boolean(anchorEl);
    const id = open ? "simple-popover" : undefined;

    return (
      <div className="dialog__container">
        <Button aria-describedby={id} variant="contained" onClick={handleClick}>
          Open Popover
        </Button>
        <Popover
          id={id}
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
        >
          <Typography sx={{ p: 2 }}>The content of the Popover.</Typography>
        </Popover>
      </div>
    );
  };

export default SimpleDialogDemo;
