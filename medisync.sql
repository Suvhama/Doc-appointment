-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 04:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medisync`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_name`, `doctor_name`, `date`, `time`, `status`) VALUES
(1, 'Alice Johnson', 'Dr. Smith', '2025-05-08', '09:00:00', ''),
(2, 'Bob Williams', 'Dr. Lee', '2025-05-08', '10:30:00', ''),
(3, 'Charlie Brown', 'Dr. Kim', '2025-05-09', '11:00:00', ''),
(4, 'Diana Evans', 'Dr. Smith', '2025-05-09', '14:00:00', 'cancelled'),
(5, 'Ethan Clark', 'Dr. Patel', '2025-05-10', '13:30:00', ''),
(6, 'Fiona Adams', 'Dr. Lee', '2025-05-10', '15:00:00', ''),
(7, 'George Harris', 'Dr. Kim', '2025-05-11', '09:45:00', ''),
(8, 'Hannah Scott', 'Dr. Patel', '2025-05-11', '11:15:00', ''),
(9, 'Ian Miller', 'Dr. Smith', '2025-05-12', '16:00:00', ''),
(10, 'Julia Roberts', 'Dr. Lee', '2025-05-12', '10:00:00', 'cancelled'),
(11, 'Rochak Maharjan', 'Dr. Diana Evans', '2025-05-07', '18:26:00', 'pending'),
(12, 'Rochak Maharjan', 'Rochak Maharjan', '2025-05-07', '22:26:00', 'confirmed'),
(13, 'Rochak Maharjan', 'Rochak Maharjan', '2025-05-09', '16:50:00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `preferred_date` date NOT NULL,
  `hospital` varchar(255) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialty` varchar(100) NOT NULL,
  `status` enum('available','busy','on-leave') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `specialty`, `status`) VALUES
(1, 'Dr. Alice Johnson', 'Cardiology', 'available'),
(2, 'Dr. Bob Williams', 'Dermatology', 'on-leave'),
(3, 'Dr. Charlie Brown', 'Neurology', 'busy'),
(4, 'Dr. Diana Evans', 'Orthopedics', ''),
(5, 'Dr. Ethan Clark', 'Pediatrics', ''),
(6, 'Dr. Fiona Adams', 'Gastroenterology', ''),
(7, 'Dr. George Harris', 'General Surgery', ''),
(8, 'Dr. Hannah Scott', 'Psychiatry', ''),
(9, 'Dr. Ian Miller', 'Oncology', ''),
(10, 'Dr. Julia Roberts', 'Radiology', ''),
(11, 'Dr. Michael Stevens', 'Cardiology', ''),
(12, 'Dr. Sarah Parker', 'Neurology', ''),
(13, 'Dr. James Watson', 'Orthopedics', ''),
(14, 'Rochak Maharjan', 'Neuro', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `record_type` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_name`, `record_type`, `date`, `status`) VALUES
(1, 'Alice Johnson', 'Blood Test', '2025-05-08', 'active'),
(2, 'Bob Williams', 'X-ray', '2025-05-08', 'archived'),
(3, 'Charlie Brown', 'MRI Scan', '2025-05-09', ''),
(4, 'Diana Evans', 'ECG', '2025-05-09', 'active'),
(5, 'Ethan Clark', 'Blood Test', '2025-05-10', 'archived'),
(6, 'Fiona Adams', 'CT Scan', '2025-05-10', 'active'),
(7, 'George Harris', 'X-ray', '2025-05-11', ''),
(8, 'Hannah Scott', 'Blood Test', '2025-05-11', 'archived'),
(9, 'Ian Miller', 'MRI Scan', '2025-05-12', 'active'),
(10, 'Julia Roberts', 'ECG', '2025-05-12', ''),
(11, 'Hero', 'Birami', '2025-05-09', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `inclusions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`inclusions`)),
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `original_price`, `discounted_price`, `image`, `inclusions`, `note`) VALUES
('bone-joint', 'Bone & Joint Checkup Package', 5500.00, 4000.00, '/images/docimage3.jpg', '[\"CBC with ESR\", \"Calcium Levels\", \"Vitamin D\", \"Rheumatoid Factor\", \"Xray (Knee/Spine)\", \"Consultation with Orthopedist\", \"Bone Density Test\"]', 'Designed to assess bone and joint health.'),
('cardiac', 'Cardiac Checkup Package', 7000.00, 5000.00, '/images/doct12.jpg', '[\"CBC with ESR\", \"Fasting Lipid Profile\", \"ECG\", \"ECHO\", \"Stress Test\", \"Consultation with Cardiologist\", \"Blood Pressure Monitoring\"]', 'Focused on heart health and cardiovascular risk assessment.'),
('child-health', 'Child Health Checkup Package', 5000.00, 3500.00, '/images/docimage.png', '[\"CBC with ESR\", \"Urine Routine\", \"Blood Group\", \"Vitamin D\", \"Iron Studies\", \"Chest Xray\", \"Pediatric Consultation\", \"Vision Screening\", \"Dental Checkup\"]', 'Comprehensive screening tailored for children under 12 years.'),
('diabetes', 'Diabetes Checkup Package', 4500.00, 3000.00, '/images/doct2.jpg', '[\"FBS, PPBS\", \"HBA1C\", \"Urine Routine\", \"Kidney Function Test\", \"Lipid Profile\", \"Consultation with Diabetologist\", \"Foot Screening\"]', 'Comprehensive monitoring for diabetes management.'),
('full-body', 'Full Body Checkup Package', 7500.00, 5500.00, '/images/doct.jpg', '[\"CBC with ESR\", \"FBS, PPBS\", \"HBA1C\", \"Fasting Lipid Profile\", \"Sr. Potassium\", \"Sr. Blood Urea\", \"Echo\", \"Consultation with Cardiologist\", \"Vitamin B12\", \"TSH\", \"Urine Routine\", \"ECG\", \"Consultation with Diabetologist\", \"Consultation with Dietician\", \"Consultation with Ophthalmologist\"]', 'Lipid Profile includes: Sr Cholesterol, HDL Cholesterol, Sr LDL Cholesterol, Sr Triglycerides & Sr VLDL Cholesterol'),
('liver-function', 'Liver Function Test Package', 4000.00, 2500.00, '/images/doct5.png', '[\"Liver Function Tests\", \"CBC with ESR\", \"Urine Routine\", \"Hepatitis B & C Screening\", \"Consultation with Hepatologist\"]', 'Liver Function Test includes: Total Bilirubin, Total Protein, Sr Albumin, Sr Globulin, A/G Ratio, GGT/SGOT/SGPT & Alkaline Phosphatase'),
('senior-citizen', 'Senior Citizen Health Package', 8000.00, 6000.00, '/images/doct2.jpg', '[\"CBC with ESR\", \"FBS/PPBS\", \"Lipid Profile\", \"Kidney Function Test\", \"Liver Function Test\", \"ECHO\", \"Chest Xray\", \"Consultation with Geriatric Specialist\", \"Vision & Hearing Test\"]', 'Tailored for individuals aged 60 and above  above.'),
('women-health', 'Women Health Checkup Package', 6000.00, 4000.00, '/images/doc5.png', '[\"CBC with ESR\", \"Urine Routine\", \"FBS/PPBS\", \"BUN\", \"Sr Creatinine\", \"Sr Electrolytes\", \"Liver Function Tests\", \"Pap Smear\", \"ECHO\", \"Chest Xray\", \"USG Abdomen & Pelvis\", \"Consultation with Gynecologist\", \"Consultation with Dietician\"]', 'Liver Function Test includes: Total Bilirubin, Total Protein, Sr Albumin, Sr Globulin, A/G Ratio, GGT/SGOT/SGPT & Alkaline Phosphatase');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `last_visit` date NOT NULL,
  `status` enum('healthy','follow-up') DEFAULT 'healthy'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `age`, `last_visit`, `status`) VALUES
(1, 'Alice Johnson', 30, '2025-05-08', ''),
(2, 'Bob Williams', 45, '2025-05-07', ''),
(3, 'Charlie Brown', 38, '2025-05-06', ''),
(4, 'Diana Evans', 50, '2025-05-05', ''),
(5, 'Ethan Clark', 29, '2025-05-04', ''),
(6, 'Fiona Adams', 40, '2025-05-03', ''),
(7, 'George Harris', 60, '2025-05-02', ''),
(8, 'Hannah Scott', 35, '2025-05-01', ''),
(9, 'Ian Miller', 55, '2025-04-30', ''),
(10, 'Julia Roberts', 41, '2025-04-29', ''),
(11, 'Rochak Maharjan', 6, '2025-05-07', 'follow-up'),
(12, 'Ronaldo', 19, '2025-05-08', 'follow-up'),
(13, 'Bob Marley', 69, '2025-04-20', 'healthy');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Rochak Maharjan ', 'Maharzanrochak7@gmail.com', '$2y$10$JvjGmEwjOTBhV6jsA7WDfOS3KNn//3Y.InXVhvdiTOK/OHghao5HC', 'admin', '2025-05-08 15:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('patient','doctor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
