import React from "react";
import useModal from "./useModal";
import Modal from "./Modal";
//https://dev.to/alexandprivate/your-next-react-modal-with-your-own-usemodal-hook-context-api-3jg7

let ModalContext;
let { Provider } = (ModalContext = React.createContext());

let ModalProvider = ({ children }) => {
  let {
    modal,
    handleModal,
    modalContent,
    modalTitle,
    isConfirm,
    confirmCallback,
  } = useModal();
  return (
    <Provider
      value={{
        modal,
        handleModal,
        modalContent,
        modalTitle,
        isConfirm,
        confirmCallback,
      }}
    >
      <Modal />
      {children}
    </Provider>
  );
};

export { ModalContext, ModalProvider };
