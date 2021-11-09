import React from "react";
import reactDom from "react-dom";
import { Dialog } from "@material-ui/core";
import { DialogContent } from "@material-ui/core";
import { DialogContentText } from "@material-ui/core";
import { DialogActions } from "@material-ui/core";
import { DialogTitle } from "@material-ui/core";
import { Button } from "@material-ui/core";
import { ModalContext } from "./modal-context";
//https://dev.to/alexandprivate/your-next-react-modal-with-your-own-usemodal-hook-context-api-3jg7

export default function MarkModal(props) {
  let {
    modalContent,
    modalTitle,
    handleModal,
    modal,
    isConfirm,
    confirmCallback,
  } = React.useContext(ModalContext);

  function handleClose() {
    handleModal();
  }
  
  function handleCloseWithCallback(response){
    handleModal();
    confirmCallback.current(response);
  }

  return (
    <React.Fragment>
      {reactDom.createPortal(
        <Dialog
          open={modal}
          onClose={handleClose}
          aria-labelledby="alert-dialog-title"
          aria-describedby="alert-dialog-description"
        >
          {modalTitle && (
            <DialogTitle id="alert-dialog-title">{modalTitle}</DialogTitle>
          )}
          <DialogContent>
            <DialogContentText id="alert-dialog-description">
              {modalContent}
            </DialogContentText>
          </DialogContent>
          <DialogActions>
            {isConfirm ? (
              <React.Fragment>
                <Button onClick={() => {handleCloseWithCallback(false)}} color="primary" autoFocus>
                  No
                </Button>
                <Button onClick={() => {handleCloseWithCallback(true)}} color="primary" autoFocus>
                  Yes
                </Button>
              </React.Fragment>
            ) : (
              <Button onClick={handleClose} color="primary" autoFocus>
                OK
              </Button>
            )}
          </DialogActions>
        </Dialog>,
        document.getElementById("modal-root")
      )}
    </React.Fragment>
  );
}
