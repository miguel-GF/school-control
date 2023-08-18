import { Loading, QSpinnerFacebook } from "quasar";

export const loading = (show, message = 'Cargando ...') => {
    show
        ? Loading.show({
              delay: 0,
              spinner: QSpinnerFacebook,
              spinnerSize: 140,
              spinnerColor: 'lime-8',
              message: message,
              backgroundColor: 'indigo-10',
              customClass: 'class-loading'
          })
        : Loading.hide();
};
