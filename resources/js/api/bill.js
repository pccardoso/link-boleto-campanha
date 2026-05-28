import axios from 'axios';

export const getAllBill = async () => {
    try{

        const response = await axios.get('/get-list-bill');
        return response.data;

    }catch(error){
        throw error;
    }

}