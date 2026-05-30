import axios from 'axios';

export const validatePlate = async (plate) => {
    try {
        const response = await axios.post('/api/history-plate', { plate_vehicle: plate });
        return response;
    } catch (error) {
        throw error;
    }
}